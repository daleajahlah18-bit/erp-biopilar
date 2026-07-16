
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
                    <tr><td><strong>Project / Customer</strong></td><td>: {{ $payment->salesInvoice->salesOrder->project->client_name ?? ($payment->salesInvoice->salesOrder->customer->customer_name ?? '-') }}</td></tr>
                    @if($payment->projectPaymentTerm)
                    <tr><td><strong>Termin / TOP</strong></td><td>: {{ $payment->projectPaymentTerm->top_type }} ({{ number_format($payment->projectPaymentTerm->percentage, 2) }}%)</td></tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Metode</strong></td><td>: {{ $payment->payment_method }}</td></tr>
                    <tr><td><strong>Nominal</strong></td><td class="text-success fw-bold fs-5">: Rp {{ number_format($payment->payment_amount, 2, ",", ".") }}</td></tr>
                    @if($payment->projectPaymentTerm)
                    <tr><td><strong>Sisa Termin</strong></td><td class="text-danger">: Rp {{ number_format($payment->projectPaymentTerm->remaining_amount, 2, ",", ".") }}</td></tr>
                    @endif
                    <tr><td><strong>Keterangan</strong></td><td>: {{ $payment->notes ?? "-" }}</td></tr>
                </table>
            </div>
        </div>
        <a href="{{ route("sales.payments.index") }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
