@extends('layouts.app')
@section('title', 'Detail Purchase Release')
@section('page_title', 'Detail Purchase Release')
@section('header_actions')
@if(!$purchase_order->goodsReceipts()->exists())
    <a href="{{ route('purchasing.purchase-orders.edit', $purchase_order) }}" class="btn-primary-custom" style="background-color: #f6c23e; border-color: #f6c23e;">
        <i class="bi bi-pencil"></i> Edit PO
    </a>
@endif
<a href="{{ route('purchasing.purchase-orders.pdf', $purchase_order) }}" class="btn-primary-custom ms-2" target="_blank">
    <i class="bi bi-file-earmark-pdf"></i> Download PDF
</a>
<a href="{{ route('purchasing.purchase-orders.index') }}" class="btn-outline-custom ms-2 text-decoration-none">Kembali</a>
@endsection

@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="mb-3">Informasi PO:</h6>
                <div><strong>Nomor PR:</strong> {{ $purchase_order->po_number }}</div>
                <div><strong>Tanggal:</strong> {{ $purchase_order->po_date }}</div>
                <div><strong>Project:</strong> {{ $purchase_order->project_note ?: '-' }}</div>
                <div><strong>Status:</strong> <span class="badge-status {{ $purchase_order->status == 'Draft' ? 'badge-draft' : 'badge-approved' }}">{{ $purchase_order->status }}</span></div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h6 class="mb-3">Supplier:</h6>
                <div><strong>{{ $purchase_order->supplier->supplier_name ?? '-' }}</strong></div>
                <div>{{ $purchase_order->supplier->supplier_address ?? '-' }}</div>
                <div>{{ $purchase_order->supplier->supplier_phone ?? '-' }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="">
                    <tr>
                        <th>No</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase_order->details as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->product_name ?? '-' }}</td>
                        <td>{{ $item->unit->unit_name ?? '-' }}</td>
                        <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Pembelian :</td>
                        <td class="text-end fw-bold">Rp {{ number_format($purchase_order->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($purchase_order->is_ppn)
                    <tr>
                        <td colspan="5" class="text-end fw-bold text-danger">PPN {{ number_format($purchase_order->ppn_percentage, 0) }}% :</td>
                        <td class="text-end fw-bold text-danger">Rp {{ number_format($purchase_order->ppn_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Grand Total :</td>
                        <td class="text-end fw-bold text-primary fs-5">Rp {{ number_format($purchase_order->grand_total ?: $purchase_order->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-secondary text-sm">
                Dibuat oleh: {{ $purchase_order->creator->name ?? 'System' }} | Waktu: {{ $purchase_order->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection
