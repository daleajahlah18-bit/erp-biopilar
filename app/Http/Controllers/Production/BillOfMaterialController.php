<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\BillOfMaterialDetail;
use App\Models\Product;
use App\Models\PurchaseOrderDetail;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillOfMaterialController extends Controller
{
    public function index()
    {
        $boms = BillOfMaterial::with(['product', 'creator'])->sortable()->latest()->sortable()->paginate(10);
        return view('production.bom.index', compact('boms'));
    }

    public function create()
    {
        $products = Product::where('product_type', 'Bahan Jadi')->get();
        $materials = Product::whereIn('product_type', ['Bahan Baku', 'Bahan Jadi', 'Bill of Material'])->get();
        return view('production.bom.create', compact('products', 'materials'));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            'bom_name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.product_id' => 'required|exists:products,id|distinct',
            'materials.*.quantity' => 'required|numeric|min:0.01',
            'materials.*.unit_cost' => 'required|numeric|min:0',
            'materials.*.subtotal' => 'required|numeric|min:0',
            'total_hpp' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $productId = $request->product_id;
            
            if ($request->has('auto_create_product') && empty($productId)) {
                $defaultUnit = \App\Models\Unit::first();
                $newProduct = \App\Models\Product::create([
                    'product_code' => $numGen->generate('PRD', \App\Models\Product::class, 'product_code'),
                    'product_name' => $request->bom_name,
                    'product_type' => 'Bahan Jadi',
                    'unit_id' => $defaultUnit ? $defaultUnit->id : 1,
                    'description' => 'Dibuat otomatis dari sistem BOM',
                    'created_by' => auth()->id()
                ]);
                $productId = $newProduct->id;
            }

            $bom = BillOfMaterial::create([
                'bom_number' => $numGen->generate('BOM', BillOfMaterial::class, 'bom_number'),
                'bom_name' => $request->bom_name,
                'product_id' => $productId,
                'total_hpp' => $request->total_hpp,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->materials as $mat) {
                // Get unit ID from product
                $product = Product::find($mat['product_id']);

                BillOfMaterialDetail::create([
                    'bill_of_material_id' => $bom->id,
                    'product_id' => $mat['product_id'],
                    'quantity' => $mat['quantity'],
                    'unit_id' => $product->unit_id,
                    'unit_cost' => $mat['unit_cost'],
                    'subtotal' => $mat['subtotal'],
                ]);
            }

            DB::commit();
            return redirect()->route('production.bom.index')->with('success', 'BOM berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan BOM: ' . $e->getMessage());
        }
    }

    public function show(BillOfMaterial $bom)
    {
        $bom->load(['product.unit', 'details.product.unit', 'creator']);
        return view('production.bom.show', compact('bom'));
    }

    public function edit(BillOfMaterial $bom)
    {
        $products = Product::where('product_type', 'Bahan Jadi')->get();
        $materials = Product::whereIn('product_type', ['Bahan Baku', 'Bahan Jadi', 'Bill of Material'])->get();
        $bom->load('details.product.unit');
        
        return view('production.bom.edit', compact('bom', 'products', 'materials'));
    }

    public function update(Request $request, BillOfMaterial $bom)
    {
        $request->validate([
            'bom_name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.product_id' => 'required|exists:products,id|distinct',
            'materials.*.quantity' => 'required|numeric|min:0.01',
            'materials.*.unit_cost' => 'required|numeric|min:0',
            'materials.*.subtotal' => 'required|numeric|min:0',
            'total_hpp' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $productId = $request->product_id;
            
            if ($request->has('auto_create_product') && empty($productId)) {
                $numGen = app(\App\Services\NumberGeneratorService::class);
                $defaultUnit = \App\Models\Unit::first();
                $newProduct = \App\Models\Product::create([
                    'product_code' => $numGen->generate('PRD', \App\Models\Product::class, 'product_code'),
                    'product_name' => $request->bom_name,
                    'product_type' => 'Bahan Jadi',
                    'unit_id' => $defaultUnit ? $defaultUnit->id : 1,
                    'description' => 'Dibuat otomatis dari sistem BOM',
                    'created_by' => auth()->id()
                ]);
                $productId = $newProduct->id;
            }

            $bom->update([
                'bom_name' => $request->bom_name,
                'product_id' => $productId,
                'total_hpp' => $request->total_hpp,
                'notes' => $request->notes,
            ]);

            // Hapus detail lama dan buat ulang
            $bom->details()->delete();

            foreach ($request->materials as $mat) {
                $product = Product::find($mat['product_id']);

                BillOfMaterialDetail::create([
                    'bill_of_material_id' => $bom->id,
                    'product_id' => $mat['product_id'],
                    'quantity' => $mat['quantity'],
                    'unit_id' => $product->unit_id,
                    'unit_cost' => $mat['unit_cost'],
                    'subtotal' => $mat['subtotal'],
                ]);
            }

            DB::commit();
            return redirect()->route('production.bom.index')->with('success', 'BOM berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui BOM: ' . $e->getMessage());
        }
    }

    public function destroy(BillOfMaterial $bom)
    {
        $bom->delete();
        return redirect()->route('production.bom.index')->with('success', 'BOM berhasil dihapus.');
    }

    public function getProductInfo($id)
    {
        $product = Product::with('unit')->find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Cari harga pembelian terakhir dari tabel purchase_order_details
        $latestPurchase = PurchaseOrderDetail::where('product_id', $id)
                            ->latest('created_at')
                            ->first();

        $unitPrice = $latestPurchase ? $latestPurchase->unit_price : 0;

        return response()->json([
            'unit_name' => $product->unit->unit_name ?? '-',
            'unit_price' => $unitPrice,
            'has_purchase_history' => $latestPurchase ? true : false
        ]);
    }
}