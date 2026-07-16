<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchasePayment;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchasePaymentController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchasePayment::class, strtolower('PurchasePayment'));
    }
    public function index()
    {
        $payments = PurchasePayment::with(['goodsReceipt.purchaseOrder.supplier', 'creator'])
            ->latest()
            ->sortable()->paginate(10);
        
        return view('purchasing.payments.index', compact('payments'));
    }

    public function create()
    {
        // Hanya ambil GR yang statusnya Unpaid atau Partially Paid
        $receipts = GoodsReceipt::whereIn('payment_status', ['Unpaid', 'Partially Paid'])
            ->with(['purchaseOrder.supplier'])
            ->get();
            
        return view('purchasing.payments.create', compact('receipts'));
    }

    public function getGrInfo($id)
    {
        $gr = GoodsReceipt::with(['purchaseOrder.supplier', 'payments'])->findOrFail($id);
        return response()->json($gr);
    }

    public function store(Request $request, NumberGeneratorService $numberGenerator)
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:Transfer Bank,Cash,Giro,Lainnya',
        ]);

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::lockForUpdate()->findOrFail($request->goods_receipt_id);

            if ($gr->payment_status === 'Paid') {
                throw new \Exception('Goods Receipt ini sudah lunas.');
            }

            if ($request->payment_amount > $gr->remaining_amount) {
                throw new \Exception('Nominal pembayaran melebihi sisa hutang.');
            }

            // Create Payment
            $payment = PurchasePayment::create([
                'payment_number' => $numberGenerator->generate('PP', PurchasePayment::class, 'payment_number'),
                'goods_receipt_id' => $gr->id,
                'payment_date' => $request->payment_date,
                'payment_amount' => $request->payment_amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Update GR
            $gr->total_paid += $request->payment_amount;
            $gr->remaining_amount -= $request->payment_amount;

            if ($gr->remaining_amount <= 0) {
                $gr->payment_status = 'Paid';
            } else {
                $gr->payment_status = 'Partially Paid';
            }

            $gr->save();

            DB::commit();

            return redirect()->route('purchasing.payments.show', $payment->id)
                ->with('success', 'Pembayaran berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(PurchasePayment $payment)
    {
        $payment->load(['goodsReceipt.purchaseOrder.supplier', 'creator']);
        return view('purchasing.payments.show', compact('payment'));
    }

    public function printPdf(PurchasePayment $payment)
    {
        $payment->load(['goodsReceipt.purchaseOrder.supplier', 'creator']);
        $pdf = Pdf::loadView('purchasing.payments.pdf', compact('payment'));
        return $pdf->download("PurchasePayment_{$payment->payment_number}.pdf");
    }
}
