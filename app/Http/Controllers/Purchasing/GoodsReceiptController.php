<?php
namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class GoodsReceiptController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(GoodsReceipt::class, strtolower('GoodsReceipt'));
    }
    public function index() {
        $receipts = GoodsReceipt::with(['purchaseOrder', 'warehouse'])->sortable()->latest()->sortable()->paginate(10);
        return view('purchasing.goods_receipts.index', compact('receipts'));
    }

    public function create() {
        $orders = PurchaseOrder::where('status', 'Approved')
                    ->whereDoesntHave('goodsReceipts')
                    ->get();
        $warehouses = Warehouse::all();
        return view('purchasing.goods_receipts.create', compact('orders', 'warehouses'));
    }

    // Endpoint for AJAX
    public function getPoDetails($id) {
        $po = PurchaseOrder::with(['details.product', 'details.unit'])->findOrFail($id);
        return response()->json($po);
    }

    public function store(Request $request, NumberGeneratorService $numberGenerator, StockMovementService $stockService) {
        $request->validate([
            'receipt_date' => 'required|date',
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'received_by' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_po' => 'required|numeric',
            'items.*.qty_received' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::create([
                'gr_number' => $numberGenerator->generate('GR', GoodsReceipt::class, 'gr_number'),
                'receipt_date' => $request->receipt_date,
                'purchase_order_id' => $request->purchase_order_id,
                'warehouse_id' => $request->warehouse_id,
                'received_by' => $request->received_by,
                'created_by' => auth()->id(),
                'total_amount' => 0, // Akan diupdate nanti
                'payment_status' => 'Unpaid',
                'total_paid' => 0,
                'remaining_amount' => 0,
                'terms_of_payment_days' => 0, // Akan diambil dari Supplier/PO
                'due_date' => null,
            ]);

            $hasReceivedAnything = false;
            $totalAmount = 0;

            // Ambil detail PO untuk mendapatkan harga satuan
            $po = PurchaseOrder::with('details')->find($request->purchase_order_id);
            // Anda mungkin perlu terms of payment supplier
            // Asumsi supplier memiliki relasi ke PO
            $po->load('supplier');
            $terms = $po->supplier->payment_terms ?? 0;

            foreach ($request->items as $item) {
                if ($item['qty_received'] > $item['qty_po']) {
                    throw new \Exception("Qty Diterima tidak boleh melebihi Qty PO untuk produk ID: " . $item['product_id']);
                }

                if ($item['qty_received'] > 0) {
                    $hasReceivedAnything = true;
                    
                    // Buat GR Detail
                    $gr->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity_order' => $item['qty_po'],
                        'quantity_received' => $item['qty_received'],
                    ]);

                    $stockService->in(
                        $item['product_id'],
                        $request->warehouse_id,
                        $item['qty_received'],
                        'Stock In',
                        $gr->gr_number,
                        'Penerimaan Barang PO'
                    );

                    // Kalkulasi total bayar
                    $poDetail = $po->details->where('product_id', $item['product_id'])->first();
                    $unitPrice = $poDetail ? $poDetail->unit_price : 0;
                    $totalAmount += ($item['qty_received'] * $unitPrice);
                }
            }

            if (!$hasReceivedAnything) {
                throw new \Exception("Minimal harus ada satu barang yang diterima (Qty > 0).");
            }

            // Update GR dengan Total Tagihan
            $dueDate = \Carbon\Carbon::parse($request->receipt_date)->addDays($terms);
            $gr->update([
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount,
                'terms_of_payment_days' => $terms,
                'due_date' => $dueDate
            ]);

            DB::commit();

            return redirect()->route('purchasing.goods-receipts.show', $gr->id)
                             ->with('success', 'Goods Receipt berhasil dibuat dan stok ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat GR: ' . $e->getMessage());
        }
    }

    public function show(GoodsReceipt $goods_receipt) {
        $goods_receipt->load(['purchaseOrder.supplier', 'warehouse', 'details.product.unit', 'creator']);
        return view('purchasing.goods_receipts.show', compact('goods_receipt'));
    }

    public function printPdf(GoodsReceipt $goods_receipt) {
        $goods_receipt->load(['purchaseOrder.supplier', 'warehouse', 'details.product.unit', 'creator']);
        $pdf = Pdf::loadView('purchasing.goods_receipts.pdf', compact('goods_receipt'));
        return $pdf->download("GR_{$goods_receipt->gr_number}.pdf");
    }

    public function edit(GoodsReceipt $goods_receipt) {}
    public function update(Request $request, GoodsReceipt $goods_receipt) {}
    public function destroy(GoodsReceipt $goods_receipt) {
        $goods_receipt->delete();
        return redirect()->route('purchasing.goods-receipts.index')->with('success', 'Deleted');
    }
}
