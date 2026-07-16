
@extends("layouts.app")
@section("title", "Detail Sales Invoice")
@section("page_title", "Sales Invoice")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Invoice: {{ $invoice->invoice_number }}</h5>
        <div class="d-flex gap-2">
            @if($invoice->status == "Draft")
                <form action="{{ route("sales.invoices.approve", $invoice->id) }}" method="POST" onsubmit="event.preventDefault(); confirmAction(() => this.submit(), 'Konfirmasi', 'Approve invoice ini?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Approve</button>
                </form>
            @endif
            <a href="{{ route("sales.invoices.pdf", $invoice->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Download PDF</a>
        </div>
    </div>
    <div class="card-body">
        @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i> Stok project sudah diproses pada menu Project Fabrication. Approval Invoice hanya berfungsi sebagai persetujuan dokumen penagihan.
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}</td></tr>
                    @php
                        $dueDate = \Carbon\Carbon::parse($invoice->invoice_date)->addDays($invoice->terms_of_payment_days)->format('d/m/Y');
                    @endphp
                    <tr><td><strong>Jatuh Tempo</strong></td><td>: {{ $dueDate }}</td></tr>
                    <tr><td><strong>Project/Customer</strong></td><td>: {{ $invoice->salesOrder->project->project_name ?? ($invoice->salesOrder->customer->customer_name ?? '-') }}</td></tr>
                    <tr><td><strong>SO Ref</strong></td><td>: <a href="{{ route("sales.orders.show", $invoice->salesOrder->id) }}">{{ $invoice->salesOrder->sales_order_number }}</a></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Status Inv</strong></td><td>: 
                        @if($invoice->status == "Draft") <span class="badge bg-secondary">Draft</span>
                        @elseif($invoice->status == "Approved") <span class="badge bg-primary">Approved</span>
                        @else <span class="badge bg-success">{{ $invoice->status }}</span> @endif
                    </td></tr>
                    <tr><td><strong>Pembayaran</strong></td><td>: 
                        @if($invoice->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                        @elseif($invoice->payment_status == "Partially Paid") <span class="badge bg-warning ">Partial</span>
                        @else <span class="badge bg-success">Paid</span> @endif
                    </td></tr>
                    <tr><td><strong>Keterangan</strong></td><td>: {{ $invoice->notes ?? "-" }}</td></tr>
                </table>
            </div>
        </div>

        @if($invoice->details && $invoice->details->count() > 0)
        <h6 class="mt-4 mb-2">Detail Produk</h6>
        <table class="table table-bordered">
            <thead class="">
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Harga (Rp)</th>
                    <th class="text-end">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $d)
                <tr>
                    <td>{{ $d->product->product_name }}</td>
                    <td>{{ number_format($d->quantity, 2, ",", ".") }}</td>
                    <td>{{ $d->unit->unit_name }}</td>
                    <td class="text-end">{{ number_format($d->unit_price, 2, ",", ".") }}</td>
                    <td class="text-end fw-bold">{{ number_format($d->subtotal, 2, ",", ".") }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total Produk:</td>
                    <td class="text-end fw-bold">Rp {{ number_format($invoice->details->sum('subtotal'), 2, ",", ".") }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        @if($invoice->services && $invoice->services->count() > 0)
        <h6 class="mt-4 mb-2">Detail Jasa</h6>
        <table class="table table-bordered">
            <thead class="">
                <tr>
                    <th>Nama Jasa</th>
                    <th>Qty</th>
                    <th>Harga (Rp)</th>
                    <th class="text-end">Subtotal (Rp)</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->services as $s)
                <tr>
                    <td>{{ $s->service_name }}</td>
                    <td>{{ number_format($s->quantity, 2, ",", ".") }}</td>
                    <td class="text-end">{{ number_format($s->unit_price, 2, ",", ".") }}</td>
                    <td class="text-end fw-bold">{{ number_format($s->subtotal, 2, ",", ".") }}</td>
                    <td>{{ $s->notes }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total Jasa:</td>
                    <td class="text-end fw-bold">Rp {{ number_format($invoice->services->sum('subtotal'), 2, ",", ".") }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @endif

        <div class="row mt-4">
            <div class="col-md-5 offset-md-7">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-end fw-bold align-middle border-top">Total Tagihan:</td>
                        <td class="text-end fw-bold text-primary fs-4 border-top">Rp {{ number_format($invoice->total_amount, 2, ",", ".") }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <a href="{{ route("sales.invoices.index") }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
