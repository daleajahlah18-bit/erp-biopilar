<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\ProjectProduction;
use App\Models\ProjectProductionDetail;
use App\Models\Project;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\ProjectProductionService;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use App\Services\ProjectCostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectProductionController extends Controller
{
    public function index()
    {
        $projectProductions = ProjectProduction::with(['project', 'warehouse', 'creator'])->sortable()->latest()->sortable()->paginate(10);
        return view('production.project_productions.index', compact('projectProductions'));
    }

    public function create()
    {
        $projects = Project::all();
        $warehouses = Warehouse::all();
        $products = Product::with('unit')->get();

        return view('production.project_productions.create', compact('projects', 'warehouses', 'products'));
    }

    public function store(Request $request, NumberGeneratorService $numGen, ProjectCostingService $costingService)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'production_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'services' => 'nullable|array',
            'services.*.service_name' => 'required|string',
            'services.*.quantity' => 'required|numeric|min:0.01',
            'services.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $projectProduction = ProjectProduction::create([
                'project_production_number' => $numGen->generate('PP', ProjectProduction::class, 'project_production_number'),
                'project_id' => $request->project_id,
                'warehouse_id' => $request->warehouse_id,
                'production_date' => $request->production_date,
                'notes' => $request->notes,
                'status' => 'Draft',
                'created_by' => auth()->id(),
            ]);

            $this->saveDetails($projectProduction, $request);

            DB::commit();
            
            // Recalculate Project HPP
            $costingService->recalculateProject($projectProduction->project);

            return redirect()->route('production.project-productions.index')->with('success', 'Project Production (Draft) berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit(ProjectProduction $projectProduction)
    {
        if ($projectProduction->status === 'Finalized') {
            return redirect()->route('production.project-productions.index')->with('error', 'Dokumen yang sudah difinalisasi tidak dapat di-edit.');
        }

        $projectProduction->load(['details.product.unit', 'services']);
        $projects = Project::all();
        $warehouses = Warehouse::all();
        $products = Product::with('unit')->get();

        return view('production.project_productions.edit', compact('projectProduction', 'projects', 'warehouses', 'products'));
    }

    public function update(Request $request, ProjectProduction $projectProduction, ProjectCostingService $costingService)
    {
        if ($projectProduction->status === 'Finalized') {
            return redirect()->route('production.project-productions.index')->with('error', 'Dokumen yang sudah difinalisasi tidak dapat di-edit.');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'production_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'services' => 'nullable|array',
            'services.*.service_name' => 'required|string',
            'services.*.quantity' => 'required|numeric|min:0.01',
            'services.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $projectProduction->update([
                'project_id' => $request->project_id,
                'warehouse_id' => $request->warehouse_id,
                'production_date' => $request->production_date,
                'notes' => $request->notes,
            ]);

            // Hapus detail yang lama
            $projectProduction->details()->delete();
            $projectProduction->services()->delete();

            // Simpan detail yang baru
            $this->saveDetails($projectProduction, $request);

            DB::commit();
            
            // Recalculate Project HPP
            $costingService->recalculateProject($projectProduction->project);

            return redirect()->route('production.project-productions.index')->with('success', 'Project Production (Draft) berhasil di-update.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function finalize(ProjectProduction $projectProduction, StockMovementService $stockService)
    {
        if ($projectProduction->status === 'Finalized') {
            return redirect()->route('production.project-productions.index')->with('error', 'Dokumen sudah difinalisasi sebelumnya.');
        }

        try {
            DB::beginTransaction();

            $projectProduction->load('details');

            foreach ($projectProduction->details as $detail) {
                // Pastikan stok mencukupi dengan lockForUpdate
                $stock = Stock::where('warehouse_id', $projectProduction->warehouse_id)
                              ->where('product_id', $detail->product_id)
                              ->lockForUpdate()
                              ->first();

                if (!$stock || $stock->quantity < $detail->quantity) {
                    throw new \Exception("Stok tidak mencukupi untuk produk ID: " . $detail->product_id);
                }

                // Update stock snapshots
                $detail->update([
                    'stock_before' => $stock->quantity,
                    'stock_after' => $stock->quantity - $detail->quantity
                ]);

                // Kurangi stok produk jadi dan simpan log ke Item Journal
                $stockService->out(
                    $detail->product_id,
                    $projectProduction->warehouse_id,
                    $detail->quantity,
                    'Stock Out',
                    $projectProduction->project_production_number,
                    'Pemakaian barang untuk project: ' . $projectProduction->project_production_number
                );
            }

            $projectProduction->update(['status' => 'Finalized']);

            DB::commit();
            
            return redirect()->route('production.project-productions.index')->with('success', 'Project Production berhasil difinalisasi. Stok telah dipotong.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal finalisasi: ' . $e->getMessage());
        }
    }

    private function saveDetails(ProjectProduction $projectProduction, Request $request)
    {
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            // Tentukan HPP Produk Jadi
            // Jika punya BOM, ambil HPP dari BOM
            $bom = \App\Models\BillOfMaterial::where('product_id', $item['product_id'])->first();
            $hpp = 0;
            
            if ($bom) {
                $hpp = $bom->total_hpp;
            } else {
                // Ambil harga beli terakhir dari PO Detail jika tidak punya BOM
                $lastPo = \App\Models\PurchaseOrderDetail::where('product_id', $item['product_id'])
                            ->latest()->first();
                $hpp = $lastPo ? $lastPR->unit_price : 0;
            }

            $materialCost = $hpp * $item['quantity'];

            ProjectProductionDetail::create([
                'project_production_id' => $projectProduction->id,
                'bill_of_material_id' => $bom ? $bom->id : null,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_id' => $product->unit_id,
                'stock_before' => 0, // Akan diupdate saat finalisasi
                'stock_after' => 0,  // Akan diupdate saat finalisasi
                'last_purchase_price' => $hpp, // untuk backward compatibility
                'bom_hpp' => $hpp, // simpan HPP snapshot
                'material_cost' => $materialCost,
            ]);
        }

        if ($request->has('services')) {
            foreach ($request->services as $svc) {
                $sub = $svc['quantity'] * $svc['unit_price'];
                ProjectProductionService::create([
                    'project_production_id' => $projectProduction->id,
                    'service_name' => $svc['service_name'],
                    'quantity' => $svc['quantity'],
                    'unit_price' => $svc['unit_price'],
                    'subtotal' => $sub,
                    'notes' => $svc['notes'] ?? null,
                ]);
            }
        }
    }

    public function show(ProjectProduction $projectProduction)
    {
        $projectProduction->load(['project', 'warehouse', 'creator', 'details.billOfMaterial', 'details.product.unit', 'services']);
        return view('production.project_productions.show', compact('projectProduction'));
    }

    public function printPdf(ProjectProduction $projectProduction)
    {
        $projectProduction->load(['project', 'warehouse', 'creator', 'details.billOfMaterial', 'details.product.unit', 'services']);

        $pdf = Pdf::loadView(
            'production.project_productions.pdf',
            compact('projectProduction')
        );

        return $pdf->download("PP_{$projectProduction->project_production_number}.pdf");
    }

    public function getProductStock($warehouse_id, $product_id)
    {
        $product = Product::with('unit')->find($product_id);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $stock = Stock::where('warehouse_id', $warehouse_id)
                      ->where('product_id', $product_id)
                      ->first();

        // Cari tahu apakah produk ini punya BOM untuk menampilkan HPP
        $bom = \App\Models\BillOfMaterial::where('product_id', $product_id)->first();
        $hpp = 0;
        if ($bom) {
            $hpp = $bom->total_hpp;
        } else {
            $lastPo = \App\Models\PurchaseOrderDetail::where('product_id', $product_id)->latest()->first();
            $hpp = $lastPo ? $lastPR->unit_price : 0;
        }

        return response()->json([
            'unit_name' => $product->unit->unit_name ?? '-',
            'stock_available' => $stock ? $stock->quantity : 0,
            'hpp' => $hpp
        ]);
    }
}
