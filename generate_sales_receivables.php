<?php

// Controller
file_put_contents('app/Http/Controllers/Sales/SalesReceivableController.php', '<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesReceivableController extends Controller
{
    public function index()
    {
        // Hanya Invoice yang sudah Approved/Paid dan bukan Draft/Cancelled
        $invoices = SalesInvoice::with(["salesOrder.customer", "payments"])
            ->whereIn("status", ["Approved", "Paid"])
            ->latest()
            ->paginate(10);

        // Calculate Summaries
        $allInvoices = SalesInvoice::with("payments")
            ->whereIn("status", ["Approved", "Paid"])
            ->get();

        $totalPiutang = 0;
        $piutangJatuhTempo = 0;
        $piutangBelumJatuhTempo = 0;
        $customerMenunggak = [];

        foreach ($allInvoices as $inv) {
            $paid = $inv->payments->sum("payment_amount");
            $sisa = $inv->total_amount - $paid;

            if ($sisa > 0) {
                $totalPiutang += $sisa;
                
                $dueDate = Carbon::parse($inv->invoice_date)->addDays($inv->terms_of_payment_days);
                if (now()->startOfDay()->greaterThan($dueDate)) {
                    $piutangJatuhTempo += $sisa;
                    $customerMenunggak[$inv->salesOrder->customer_id] = true;
                } else {
                    $piutangBelumJatuhTempo += $sisa;
                }
            }
        }

        $summary = [
            "total_piutang" => $totalPiutang,
            "piutang_jatuh_tempo" => $piutangJatuhTempo,
            "piutang_belum_jatuh_tempo" => $piutangBelumJatuhTempo,
            "jumlah_customer_menunggak" => count($customerMenunggak)
        ];

        return view("sales.receivables.index", compact("invoices", "summary"));
    }

    public function show(SalesInvoice $invoice)
    {
        $invoice->load(["salesOrder.customer", "payments.creator"]);
        return view("sales.receivables.show", compact("invoice"));
    }
}
');

mkdir('resources/views/sales/receivables', 0777, true);

// Views: Index
file_put_contents('resources/views/sales/receivables/index.blade.php', '
@extends("layouts.app")
@section("title", "Sales Receivable")
@section("page_title", "Monitoring Piutang")

@section("content")
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6>Total Piutang Berjalan</h6>
                <h4 class="mb-0">Rp {{ number_format($summary["total_piutang"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h6>Piutang Jatuh Tempo</h6>
                <h4 class="mb-0">Rp {{ number_format($summary["piutang_jatuh_tempo"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6>Piutang Belum Jatuh Tempo</h6>
                <h4 class="mb-0">Rp {{ number_format($summary["piutang_belum_jatuh_tempo"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h6>Customer Menunggak</h6>
                <h4 class="mb-0">{{ $summary["jumlah_customer_menunggak"] }} Customer</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Tagihan (Receivables)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Inv Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Total Invoice</th>
                        <th class="text-end">Dibayar</th>
                        <th class="text-end">Sisa Tagihan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $paid = $inv->payments->sum("payment_amount");
                            $sisa = $inv->total_amount - $paid;
                            $dueDate = \Carbon\Carbon::parse($inv->invoice_date)->addDays($inv->terms_of_payment_days);
                            $isOverdue = $sisa > 0 && now()->startOfDay()->greaterThan($dueDate);
                        @endphp
                        <tr style="cursor: pointer;" onclick="window.location=\'{{ route(\'sales.receivables.show\', $inv->id) }}\'">
                            <td><a href="{{ route("sales.receivables.show", $inv->id) }}">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->salesOrder->customer->customer_name }}</td>
                            <td>{{ date("d/m/Y", strtotime($inv->invoice_date)) }}</td>
                            <td>
                                {{ $dueDate->format("d/m/Y") }}
                                @if($isOverdue) <span class="badge bg-danger rounded-pill ms-1">Overdue</span> @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($inv->total_amount, 2, ",", ".") }}</td>
                            <td class="text-end text-success">Rp {{ number_format($paid, 2, ",", ".") }}</td>
                            <td class="text-end text-danger fw-bold">Rp {{ number_format($sisa, 2, ",", ".") }}</td>
                            <td>
                                @if($inv->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                                @elseif($inv->payment_status == "Partially Paid") <span class="badge bg-warning text-dark">Partial</span>
                                @else <span class="badge bg-success">Paid</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Belum ada data piutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
');

// Views: Show (Histori)
file_put_contents('resources/views/sales/receivables/show.blade.php', '
@extends("layouts.app")
@section("title", "Histori Piutang")
@section("page_title", "Histori Pembayaran Piutang")

@section("content")
@php
    $paid = $invoice->payments->sum("payment_amount");
    $sisa = $invoice->total_amount - $paid;
@endphp
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Invoice: <a href="{{ route("sales.invoices.show", $invoice->id) }}">{{ $invoice->invoice_number }}</a></h5>
                <p class="mb-1"><strong>Customer:</strong> {{ $invoice->salesOrder->customer->customer_name }}</p>
                <p class="mb-1"><strong>Tanggal Invoice:</strong> {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}</p>
                <p class="mb-0"><strong>Status Pembayaran:</strong> 
                    @if($invoice->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                    @elseif($invoice->payment_status == "Partially Paid") <span class="badge bg-warning text-dark">Partial</span>
                    @else <span class="badge bg-success">Paid</span> @endif
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h6 class="text-muted">Total Invoice</h6>
                <h4 class="text-primary">Rp {{ number_format($invoice->total_amount, 2, ",", ".") }}</h4>
                <hr>
                <div class="d-flex justify-content-end gap-5">
                    <div>
                        <h6 class="text-muted">Total Dibayar</h6>
                        <h5 class="text-success">Rp {{ number_format($paid, 2, ",", ".") }}</h5>
                    </div>
                    <div>
                        <h6 class="text-muted">Sisa Tagihan</h6>
                        <h5 class="text-danger">Rp {{ number_format($sisa, 2, ",", ".") }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Riwayat Pembayaran Cicilan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No Tanda Terima</th>
                        <th>Tanggal Pembayaran</th>
                        <th>Metode</th>
                        <th class="text-end">Nominal (Rp)</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->payments as $pay)
                        <tr>
                            <td><a href="{{ route("sales.payments.show", $pay->id) }}">{{ $pay->payment_number }}</a></td>
                            <td>{{ date("d/m/Y", strtotime($pay->payment_date)) }}</td>
                            <td>{{ $pay->payment_method }}</td>
                            <td class="text-end text-success fw-bold">{{ number_format($pay->payment_amount, 2, ",", ".") }}</td>
                            <td>{{ $pay->creator->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada pembayaran yang diterima.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a href="{{ route("sales.receivables.index") }}" class="btn btn-secondary mt-3">Kembali ke Dashboard Piutang</a>
    </div>
</div>
@endsection
');
