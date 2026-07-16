<?php

// Controller
file_put_contents('app/Http/Controllers/Sales/SalesPaymentController.php', '<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesPayment;
use App\Models\SalesInvoice;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesPaymentController extends Controller
{
    public function index()
    {
        $payments = SalesPayment::with(["salesInvoice.salesOrder.customer", "creator"])->latest()->paginate(10);
        return view("sales.payments.index", compact("payments"));
    }

    public function create()
    {
        // Hanya Invoice yang sudah Approved dan belum Paid
        $invoices = SalesInvoice::where("status", "Approved")
                                ->whereIn("payment_status", ["Unpaid", "Partially Paid"])
                                ->get();
        return view("sales.payments.create", compact("invoices"));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            "sales_invoice_id" => "required|exists:sales_invoices,id",
            "payment_date" => "required|date",
            "payment_amount" => "required|numeric|min:1",
            "payment_method" => "required|string",
            "notes" => "nullable|string",
        ]);

        try {
            DB::beginTransaction();

            $invoice = SalesInvoice::lockForUpdate()->find($request->sales_invoice_id);

            // Cek total yang sudah dibayar
            $alreadyPaid = $invoice->payments()->sum("payment_amount");
            $sisaTagihan = $invoice->total_amount - $alreadyPaid;

            if ($request->payment_amount > $sisaTagihan) {
                throw new \Exception("Nominal pembayaran melebih sisa tagihan! Sisa: Rp " . number_format($sisaTagihan, 2));
            }

            $payment = SalesPayment::create([
                "payment_number" => $numGen->generate("PAY-SO", SalesPayment::class, "payment_number"),
                "sales_invoice_id" => $request->sales_invoice_id,
                "payment_date" => $request->payment_date,
                "payment_amount" => $request->payment_amount,
                "payment_method" => $request->payment_method,
                "notes" => $request->notes,
                "created_by" => auth()->id()
            ]);

            // Update status pembayaran invoice
            $totalPaidNow = $alreadyPaid + $request->payment_amount;
            if ($totalPaidNow >= $invoice->total_amount) {
                $invoice->update(["payment_status" => "Paid", "status" => "Paid"]); // Status invoice juga jadi Paid
            } else {
                $invoice->update(["payment_status" => "Partially Paid"]);
            }

            DB::commit();
            return redirect()->route("sales.payments.index")->with("success", "Pembayaran berhasil dicatat.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function show(SalesPayment $payment)
    {
        $payment->load(["salesInvoice.salesOrder.customer", "creator"]);
        return view("sales.payments.show", compact("payment"));
    }

    public function printPdf(SalesPayment $payment)
    {
        $payment->load(["salesInvoice.salesOrder.customer", "creator"]);
        $pdf = Pdf::loadView("sales.payments.pdf", compact("payment"));
        return $pdf->download($payment->payment_number . ".pdf");
    }

    public function getInvoiceInfo($id)
    {
        $invoice = SalesInvoice::with(["salesOrder.customer", "payments"])->find($id);
        if (!$invoice) return response()->json(["error" => "Invoice not found"], 404);

        $alreadyPaid = $invoice->payments->sum("payment_amount");
        $remaining = $invoice->total_amount - $alreadyPaid;

        return response()->json([
            "customer_name" => $invoice->salesOrder->customer->customer_name,
            "total_invoice" => $invoice->total_amount,
            "total_paid" => $alreadyPaid,
            "remaining_balance" => $remaining
        ]);
    }
}
');

mkdir('resources/views/sales/payments', 0777, true);

// Views: Index
file_put_contents('resources/views/sales/payments/index.blade.php', '
@extends("layouts.app")
@section("title", "Sales Payment")
@section("page_title", "Sales Payment")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penerimaan Pembayaran</h5>
        <a href="{{ route("sales.payments.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Terima Pembayaran
        </a>
    </div>
    <div class="card-body">
        @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="table-light">
                    <tr>
                        <th>No Payment</th>
                        <th>Tanggal</th>
                        <th>Invoice Ref</th>
                        <th>Customer</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                        <tr>
                            <td>{{ $pay->payment_number }}</td>
                            <td>{{ date("d/m/Y", strtotime($pay->payment_date)) }}</td>
                            <td>{{ $pay->salesInvoice->invoice_number }}</td>
                            <td>{{ $pay->salesInvoice->salesOrder->customer->customer_name }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($pay->payment_amount, 2, ",", ".") }}</td>
                            <td>{{ $pay->payment_method }}</td>
                            <td>
                                <a href="{{ route("sales.payments.show", $pay->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada data pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
');

// Views: Create
file_put_contents('resources/views/sales/payments/create.blade.php', '
@extends("layouts.app")
@section("title", "Terima Sales Payment")
@section("page_title", "Sales Payment")
@section("page_subtitle", "Terima Pembayaran")

@section("content")
<div class="card">
    <div class="card-body">
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        <form action="{{ route("sales.payments.store") }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Pilih Invoice *</label>
                    <select name="sales_invoice_id" id="sales_invoice_id" class="form-select" required>
                        <option value="">-- Pilih Invoice --</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }} ({{ $inv->salesOrder->customer->customer_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Tanggal Bayar *</label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ date("Y-m-d") }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Metode Pembayaran *</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Cash">Cash</option>
                        <option value="Giro">Giro</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="row bg-light p-3 rounded mb-3">
                <div class="col-md-3">
                    <label class="text-muted">Customer</label>
                    <h6 id="display_customer">-</h6>
                </div>
                <div class="col-md-3">
                    <label class="text-muted">Total Invoice</label>
                    <h6 id="display_total" class="text-primary">-</h6>
                </div>
                <div class="col-md-3">
                    <label class="text-muted">Sudah Dibayar</label>
                    <h6 id="display_paid" class="text-success">-</h6>
                </div>
                <div class="col-md-3">
                    <label class="text-muted">Sisa Tagihan</label>
                    <h6 id="display_remaining" class="text-danger">-</h6>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nominal Pembayaran (Rp) *</label>
                    <input type="number" name="payment_amount" id="payment_amount" class="form-control fs-4 fw-bold text-success" required min="1" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Keterangan / Referensi</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Transfer BCA a/n Budi">
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn-primary-custom px-4">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const invSelect = document.getElementById("sales_invoice_id");

    invSelect.addEventListener("change", function() {
        const invId = this.value;
        if(!invId) {
            document.getElementById("display_customer").textContent = "-";
            document.getElementById("display_total").textContent = "-";
            document.getElementById("display_paid").textContent = "-";
            document.getElementById("display_remaining").textContent = "-";
            document.getElementById("payment_amount").value = 0;
            return;
        }

        fetch(`/sales/payments/api/invoice-info/${invId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("display_customer").textContent = data.customer_name;
            document.getElementById("display_total").textContent = "Rp " + parseFloat(data.total_invoice).toLocaleString("id-ID", {minimumFractionDigits:2});
            document.getElementById("display_paid").textContent = "Rp " + parseFloat(data.total_paid).toLocaleString("id-ID", {minimumFractionDigits:2});
            document.getElementById("display_remaining").textContent = "Rp " + parseFloat(data.remaining_balance).toLocaleString("id-ID", {minimumFractionDigits:2});
            
            // Set max value
            document.getElementById("payment_amount").value = data.remaining_balance;
            document.getElementById("payment_amount").max = data.remaining_balance;
        });
    });
});
</script>
');

// Views: Show
file_put_contents('resources/views/sales/payments/show.blade.php', '
@extends("layouts.app")
@section("title", "Detail Sales Payment")
@section("page_title", "Sales Payment")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Payment: {{ $payment->payment_number }}</h5>
        <div>
            <a href="{{ route("sales.payments.pdf", $payment->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Download Receipt</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($payment->payment_date)) }}</td></tr>
                    <tr><td><strong>Invoice Ref</strong></td><td>: <a href="{{ route("sales.invoices.show", $payment->salesInvoice->id) }}">{{ $payment->salesInvoice->invoice_number }}</a></td></tr>
                    <tr><td><strong>Customer</strong></td><td>: {{ $payment->salesInvoice->salesOrder->customer->customer_name }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Metode</strong></td><td>: {{ $payment->payment_method }}</td></tr>
                    <tr><td><strong>Nominal</strong></td><td class="text-success fw-bold fs-5">: Rp {{ number_format($payment->payment_amount, 2, ",", ".") }}</td></tr>
                    <tr><td><strong>Keterangan</strong></td><td>: {{ $payment->notes ?? "-" }}</td></tr>
                </table>
            </div>
        </div>
        <a href="{{ route("sales.payments.index") }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
');

// Views: PDF
file_put_contents('resources/views/sales/payments/pdf.blade.php', '<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt {{ $payment->payment_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .box { border: 1px solid #000; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>OFFICIAL RECEIPT / KUITANSI</h2>
        <h3>PT BIO PILAR UTAMA</h3>
    </div>
    
    <div class="box">
        <table style="width: 100%;">
            <tr><td width="25%"><strong>No Tanda Terima</strong></td><td>: {{ $payment->payment_number }}</td></tr>
            <tr><td><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($payment->payment_date)) }}</td></tr>
            <tr><td><strong>Telah Terima Dari</strong></td><td>: {{ $payment->salesInvoice->salesOrder->customer->customer_name }}</td></tr>
            <tr><td><strong>Uang Sejumlah</strong></td><td>: <strong>Rp {{ number_format($payment->payment_amount, 2) }}</strong></td></tr>
            <tr><td><strong>Untuk Pembayaran</strong></td><td>: Tagihan Invoice {{ $payment->salesInvoice->invoice_number }}</td></tr>
            <tr><td><strong>Metode Pembayaran</strong></td><td>: {{ $payment->payment_method }}</td></tr>
            <tr><td><strong>Keterangan</strong></td><td>: {{ $payment->notes }}</td></tr>
        </table>
    </div>

    <table style="width:100%; text-align:center; margin-top:50px;">
        <tr>
            <td width="50%">Penyetor / Customer<br><br><br><br>( ............................ )</td>
            <td width="50%">Penerima / PT Bio Pilar<br><br><br><br>( ............................ )</td>
        </tr>
    </table>
</body>
</html>
');
