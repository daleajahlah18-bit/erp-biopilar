<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\Product;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(StockOpname::class, strtolower('StockOpname'));
    }
    public function index()
    {
        $opnames = StockOpname::with('warehouse')->sortable()->latest()->sortable()->paginate(10);
        return view('inventory.stock_opnames.index', compact('opnames'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('inventory.stock_opnames.create', compact('warehouses'));
    }

    public function getProducts($warehouse_id)
    {
        $products = Product::with('unit')->get();
        $stocks = Stock::where('warehouse_id', $warehouse_id)->get()->keyBy('product_id');

        $result = $products->map(function($product) use ($stocks) {
            $systemStock = $stocks->has($product->id) ? $stocks[$product->id]->quantity : 0;
            return [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'unit_name' => $product->unit->unit_name ?? '-',
                'system_stock' => $systemStock
            ];
        });

        return response()->json(['products' => $result]);
    }

    public function store(Request $request, NumberGeneratorService $numGen, StockMovementService $stockService)
    {
        $request->validate([
            'opname_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.system_stock' => 'required|numeric',
            'items.*.physical_stock' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $opname = StockOpname::create([
                'opname_number' => $numGen->generate('OPN', StockOpname::class, 'opname_number'),
                'warehouse_id' => $request->warehouse_id,
                'opname_date' => $request->opname_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $diff = $item['physical_stock'] - $item['system_stock'];

                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $item['product_id'],
                    'system_stock' => $item['system_stock'],
                    'physical_stock' => $item['physical_stock'],
                    'difference' => $diff,
                ]);

                if ($diff > 0) {
                    $stockService->in(
                        $item['product_id'],
                        $request->warehouse_id,
                        abs($diff),
                        'Adjustment',
                        $opname->opname_number,
                        'Penyesuaian Opname Plus'
                    );
                } elseif ($diff < 0) {
                    $stockService->out(
                        $item['product_id'],
                        $request->warehouse_id,
                        abs($diff),
                        'Adjustment',
                        $opname->opname_number,
                        'Penyesuaian Opname Minus'
                    );
                }
            }

            DB::commit();
            return redirect()->route('inventory.stock-opnames.index')->with('success', 'Stock Opname berhasil disimpan dan stok telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses Stock Opname: ' . $e->getMessage());
        }
    }

    public function show(StockOpname $stock_opname)
    {
        $stock_opname->load(['warehouse', 'creator', 'details.product.unit']);
        return view('inventory.stock_opnames.show', compact('stock_opname'));
    }

    public function edit(StockOpname $stock_opname) {}
    public function update(Request $request, StockOpname $stock_opname) {}
    public function destroy(StockOpname $stock_opname) {}
}
