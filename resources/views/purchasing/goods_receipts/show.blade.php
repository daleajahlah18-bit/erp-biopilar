@extends('layouts.app')
@section('title', 'Detail Goods Receipt')
@section('page_title', 'Detail Goods Receipt')
@section('header_actions')
<a href="{{ route('purchasing.goods-receipts.pdf', $goods_receipt) }}" class="btn-primary-custom" target="_blank">
    <i class="bi bi-file-earmark-pdf"></i> Download PDF
</a>
<a href="{{ route('purchasing.goods-receipts.index') }}" class="btn-outline-custom ms-2 text-decoration-none">Kembali</a>
@endsection

@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="mb-3 text-primary fw-bold">Informasi GR:</h6>
                <div><strong>Nomor GR:</strong> {{ $goods_receipt->gr_number }}</div>
                <div><strong>Tanggal Terima:</strong> {{ $goods_receipt->receipt_date }}</div>
                <div><strong>Penerima:</strong> {{ $goods_receipt->received_by }}</div>
                <div><strong>Gudang Tujuan:</strong> {{ $goods_receipt->warehouse->warehouse_name ?? '-' }}</div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h6 class="mb-3 text-primary fw-bold">Referensi PO:</h6>
                <div><strong>Nomor PR:</strong> <a href="{{ route('purchasing.purchase-orders.show', $goods_receipt->purchase_order_id) }}">{{ $goods_receipt->purchaseOrder->po_number ?? '-' }}</a></div>
                <div><strong>Supplier:</strong> {{ $goods_receipt->purchaseOrder->supplier->supplier_name ?? '-' }}</div>
            </div>
        </div>

        <h6 class="mb-3 text-primary fw-bold">Daftar Barang Diterima:</h6>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="40%">Product</th>
                        <th width="15%">Unit</th>
                        <th class="text-end" width="20%">Qty PO</th>
                        <th class="text-end text-success" width="20%">Qty Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goods_receipt->details as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->product_name ?? '-' }}</td>
                        <td>{{ $item->product->unit->unit_name ?? '-' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity_order, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="text-end fw-bold text-success">{{ rtrim(rtrim(number_format($item->quantity_received, 2, ',', '.'), '0'), ',') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-secondary text-sm">
                Dibuat oleh: {{ $goods_receipt->creator->name ?? 'System' }} | Waktu: {{ $goods_receipt->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection
