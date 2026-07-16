<?php
namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransfer;
use App\Models\InventoryTransferDetail;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransferController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(InventoryTransfer::class, strtolower('InventoryTransfer'));
    }
    public function index() 
    { 
        $transfers = InventoryTransfer::with(['sourceWarehouse', 'destinationWarehouse'])->sortable()->latest()->sortable()->paginate(10); 
        return view('inventory.transfers.index', compact('transfers')); 
    }

    public function create() 
    { 
        $warehouses = Warehouse::all();
        return view('inventory.transfers.create', compact('warehouses')); 
    }

    public function store(Request $request, NumberGeneratorService $numGen, StockMovementService $stockService) 
    {
        $request->validate([
            'transfer_date' => 'required|date',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'destination_warehouse_id.different' => 'Gudang Tujuan tidak boleh sama dengan Gudang Asal.',
            'items.min' => 'Minimal harus ada satu item yang ditransfer.'
        ]);

        try {
            DB::beginTransaction();

            $transfer = InventoryTransfer::create([
                'transfer_number' => $numGen->generate('TRF', InventoryTransfer::class, 'transfer_number'),
                'transfer_date' => $request->transfer_date,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                // Pastikan stok mencukupi
                $stock = Stock::where('warehouse_id', $request->source_warehouse_id)
                              ->where('product_id', $item['product_id'])
                              ->lockForUpdate()
                              ->first();

                if (!$stock || $stock->quantity < $item['quantity']) {
                    throw new \Exception("Stok tidak mencukupi untuk salah satu produk yang dipilih.");
                }

                InventoryTransferDetail::create([
                    'inventory_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Kurangi stok gudang asal
                $stockService->out(
                    $item['product_id'],
                    $request->source_warehouse_id,
                    $item['quantity'],
                    'Transfer Out',
                    $transfer->transfer_number,
                    'Transfer ke Gudang ID: ' . $request->destination_warehouse_id
                );

                // Tambah stok gudang tujuan
                $stockService->in(
                    $item['product_id'],
                    $request->destination_warehouse_id,
                    $item['quantity'],
                    'Transfer In',
                    $transfer->transfer_number,
                    'Transfer dari Gudang ID: ' . $request->source_warehouse_id
                );
            }

            DB::commit();
            return redirect()->route('inventory.transfers.index')->with('success', 'Transfer Stok berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan Transfer Stok: ' . $e->getMessage());
        }
    }

    public function show(InventoryTransfer $inventory_transfer) {
        $inventory_transfer->load(['sourceWarehouse', 'destinationWarehouse', 'details.product.unit', 'creator']);
        return view('inventory.transfers.show', compact('inventory_transfer'));
    }

    // AJAX Endpoint 1: Ambil produk yang ada stoknya di gudang tertentu
    public function getProductsByWarehouse($warehouseId)
    {
        $stocks = Stock::with(['product.unit'])
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->get();
            
        return response()->json(['stocks' => $stocks]);
    }

    // AJAX Endpoint 2: Ambil spesifik stok sebuah produk di gudang tertentu
    public function getProductStock($warehouseId, $productId)
    {
        $stock = Stock::with('product.unit')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
            
        return response()->json([
            'quantity' => $stock ? $stock->quantity : 0,
            'unit_name' => ($stock && $stock->product && $stock->product->unit) ? $stock->product->unit->unit_name : '-'
        ]);
    }
}