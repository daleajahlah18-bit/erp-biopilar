<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionOrderController extends Controller
{
    public function index()
    {
        $orders = ProductionOrder::with(['billOfMaterial.product', 'warehouse', 'creator'])->sortable()->latest()->sortable()->paginate(10);
        return view('production.orders.index', compact('orders'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $boms = BillOfMaterial::with('product')->get();
        $products = Product::with('unit')->get();

        return view('production.orders.create', compact('warehouses', 'boms', 'products'));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            'production_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'bill_of_material_id' => 'required|exists:bill_of_materials,id',
            'target_quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.product_id' => 'required|exists:products,id',
            'materials.*.quantity_per_bom' => 'required|numeric|min:0',
            'materials.*.quantity_required' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $order = ProductionOrder::create([
                'production_number' => $numGen->generate('PRD', ProductionOrder::class, 'production_number'),
                'bill_of_material_id' => $request->bill_of_material_id,
                'warehouse_id' => $request->warehouse_id,
                'target_quantity' => $request->target_quantity,
                'production_date' => $request->production_date,
                'status' => 'Draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->materials as $mat) {
                $stock = Stock::where('warehouse_id', $request->warehouse_id)
                              ->where('product_id', $mat['product_id'])
                              ->first();

                ProductionOrderDetail::create([
                    'production_order_id' => $order->id,
                    'product_id' => $mat['product_id'],
                    'quantity_per_bom' => $mat['quantity_per_bom'],
                    'quantity_required' => $mat['quantity_required'],
                    'stock_available' => $stock ? $stock->quantity : 0,
                ]);
            }

            DB::commit();
            return redirect()->route('production.orders.index')->with('success', 'Draft Production Order berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan Draft Produksi: ' . $e->getMessage());
        }
    }

    public function show(ProductionOrder $order)
    {
        $order->load(['billOfMaterial.product', 'warehouse', 'details.product.unit', 'creator']);
        return view('production.orders.show', compact('order'));
    }

    public function startProduction(ProductionOrder $order, StockMovementService $stockService)
    {
        if ($order->status !== 'Draft') {
            return back()->with('error', 'Hanya order berstatus Draft yang bisa dimulai.');
        }

        try {
            DB::beginTransaction();

            $order->load('details');

            foreach ($order->details as $detail) {
                // Pastikan stok mencukupi dengan lockForUpdate
                $stock = Stock::where('warehouse_id', $order->warehouse_id)
                              ->where('product_id', $detail->product_id)
                              ->lockForUpdate()
                              ->first();

                if (!$stock || $stock->quantity < $detail->quantity_required) {
                    throw new \Exception("Stok tidak mencukupi untuk bahan baku ID: " . $detail->product_id);
                }

                // Kurangi stok bahan baku (Raw Material)
                $stockService->out(
                    $detail->product_id,
                    $order->warehouse_id,
                    $detail->quantity_required,
                    'Stock Out',
                    $order->production_number,
                    'Produksi Barang: ' . $order->production_number
                );
            }

            $order->update(['status' => 'In Progress']);

            DB::commit();
            return back()->with('success', 'Produksi dimulai. Stok bahan baku telah dipotong.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai produksi: ' . $e->getMessage());
        }
    }

    public function productionResult(ProductionOrder $order)
    {
        if ($order->status !== 'In Progress') {
            return redirect()->route('production.orders.show', $order->id)->with('error', 'Status harus In Progress untuk mengisi hasil.');
        }

        $order->load('billOfMaterial.product');
        return view('production.orders.result', compact('order'));
    }

    public function saveProductionResult(Request $request, ProductionOrder $order, StockMovementService $stockService)
    {
        if ($order->status !== 'In Progress') {
            return redirect()->route('production.orders.show', $order->id)->with('error', 'Status sudah tidak In Progress.');
        }

        $request->validate([
            'actual_quantity' => 'required|numeric|min:0',
            'production_result_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $order->update([
                'actual_quantity' => $request->actual_quantity,
                'production_result_notes' => $request->production_result_notes,
                'status' => 'Completed'
            ]);

            // Jika BOM memiliki Produk Jadi, tambahkan stok produk jadinya
            $bom = $order->billOfMaterial;
            if ($bom && $bom->product_id && $request->actual_quantity > 0) {
                $stockService->in(
                    $bom->product_id,
                    $order->warehouse_id,
                    $request->actual_quantity,
                    'Stock In',
                    $order->production_number,
                    'Hasil Produksi Aktual: ' . $order->production_number
                );
            }

            DB::commit();
            return redirect()->route('production.orders.show', $order->id)->with('success', 'Hasil Produksi disimpan. Status menjadi Completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan hasil: ' . $e->getMessage());
        }
    }

    public function getBomMaterials($bom_id)
    {
        $bom = BillOfMaterial::with(['details.product.unit'])->find($bom_id);
        
        if (!$bom) {
            return response()->json(['error' => 'BOM tidak ditemukan'], 404);
        }

        $materials = [];
        foreach ($bom->details as $detail) {
            $materials[] = [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product->product_name ?? 'Unknown',
                'product_code' => $detail->product->product_code ?? '-',
                'unit_name' => $detail->product->unit->unit_name ?? '-',
                'quantity_per_bom' => $detail->quantity
            ];
        }

        return response()->json(['materials' => $materials]);
    }

    public function getWarehouseStock($warehouse_id, $product_id)
    {
        $product = Product::with('unit')->find($product_id);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $stock = Stock::where('warehouse_id', $warehouse_id)
                      ->where('product_id', $product_id)
                      ->first();

        return response()->json([
            'unit_name' => $product->unit->unit_name ?? '-',
            'product_name' => $product->product_name,
            'product_code' => $product->product_code,
            'stock_available' => $stock ? $stock->quantity : 0
        ]);
    }
}