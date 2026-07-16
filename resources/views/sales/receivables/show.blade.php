
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
                <p class="mb-1"><strong>Project / Customer:</strong> {{ $invoice->salesOrder->project->client_name ?? ($invoice->salesOrder->customer->customer_name ?? '-') }}</p>
                <p class="mb-1"><strong>Tanggal Invoice:</strong> {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}</p>
                <p class="mb-0"><strong>Status Pembayaran:</strong> 
                    @if($invoice->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                    @elseif($invoice->payment_status == "Partially Paid") <span class="badge bg-warning ">Partial</span>
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
                <thead class="">
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
