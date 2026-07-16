
@extends("layouts.app")
@section("title", "Detail Sales Order")
@section("page_title", "Sales Order")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Sales Order: {{ $order->sales_order_number }}</h5>
        <div>
            <a href="{{ route("sales.orders.pdf", $order->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Download PDF</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($order->sales_order_date)) }}</td></tr>
                    <tr><td><strong>Project/Customer</strong></td><td>: {{ $order->project->project_name ?? ($order->customer->customer_name ?? '-') }}</td></tr>
                    <tr><td><strong>PIC</strong></td><td>: {{ $order->project->person_in_charge ?? ($order->customer->customer_pic ?? "-") }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Status</strong></td><td>: {{ $order->status }}</td></tr>
                    <tr><td><strong>Keterangan</strong></td><td>: {{ $order->notes ?? "-" }}</td></tr>
                </table>
            </div>
        </div>

        @if($order->details && $order->details->count() > 0)
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
                @foreach($order->details as $d)
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
                    <td class="text-end fw-bold">Rp {{ number_format($order->details->sum('subtotal'), 2, ",", ".") }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        @if($order->services && $order->services->count() > 0)
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
                @foreach($order->services as $s)
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
                    <td class="text-end fw-bold">Rp {{ number_format($order->services->sum('subtotal'), 2, ",", ".") }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @endif

        <div class="row mt-4">
            <div class="col-md-5 offset-md-7">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-end fw-bold align-middle border-top">Grand Total:</td>
                        <td class="text-end fw-bold text-primary fs-4 border-top">Rp {{ number_format($order->total_amount, 2, ",", ".") }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <a href="{{ route("sales.orders.index") }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
