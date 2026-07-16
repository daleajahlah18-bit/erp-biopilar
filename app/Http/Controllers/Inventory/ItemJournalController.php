<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ItemJournal;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemJournalController extends Controller
{
    public function index()
    {
        $journals = ItemJournal::with(['product', 'warehouse'])->sortable()->latest()->sortable()->paginate(15);
        return view('inventory.item_journals.index', compact('journals'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('inventory.item_journals.create', compact('warehouses', 'products'));
    }

    public function store(Request $request, StockMovementService $stockService)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|in:STOCK_IN,STOCK_OUT',
            'quantity' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $type = $request->transaction_type == 'STOCK_IN' ? 'Stock In' : 'Stock Out';

            if ($type == 'Stock Out') {
                $stock = Stock::where('warehouse_id', $request->warehouse_id)
                              ->where('product_id', $request->product_id)
                              ->lockForUpdate()
                              ->first();

                if (!$stock || $stock->quantity < $request->quantity) {
                    throw new \Exception('Stok tidak mencukupi untuk transaksi STOCK_OUT.');
                }

                $stockService->out(
                    $request->product_id,
                    $request->warehouse_id,
                    $request->quantity,
                    $type,
                    'Manual',
                    $request->description
                );
            } else {
                $stockService->in(
                    $request->product_id,
                    $request->warehouse_id,
                    $request->quantity,
                    $type,
                    'Manual',
                    $request->description
                );
            }

            // Memperbarui tanggal jurnal manual karena StockMovementService mungkin menggunakan now()
            $lastJournal = ItemJournal::latest('id')->first();
            if ($lastJournal) {
                $lastJournal->update(['transaction_date' => $request->transaction_date]);
            }

            DB::commit();
            return redirect()->route('inventory.item-journals.index')->with('success', 'Manual Item Journal berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(ItemJournal $item_journal)
    {
        $item_journal->load(['product.unit', 'warehouse']);
        return view('inventory.item_journals.show', compact('item_journal'));
    }
}
