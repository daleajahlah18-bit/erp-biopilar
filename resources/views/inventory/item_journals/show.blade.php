@extends('layouts.app')
@section('title', 'Detail Item Journal')
@section('page_title', 'Item Journal')
@section('page_subtitle', $item_journal->journal_number)

@section('header_actions')
<a href="{{ route('inventory.item-journals.index') }}" class="btn-outline-custom">Kembali</a>
@endsection

@section('content')
<div class="card mb-4" style="max-width: 800px;">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">No Jurnal</label>
                <div><strong>{{ $item_journal->journal_number }}</strong></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Tanggal Transaksi</label>
                <div><strong>{{ \Carbon\Carbon::parse($item_journal->transaction_date)->format('d/m/Y') }}</strong></div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Jenis Transaksi</label>
                <div>
                    @if($item_journal->transaction_type == 'Stock In')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success">{{ $item_journal->transaction_type }}</span>
                    @elseif($item_journal->transaction_type == 'Stock Out')
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">{{ $item_journal->transaction_type }}</span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $item_journal->transaction_type }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Referensi</label>
                <div><strong>{{ $item_journal->reference_number ?? 'Manual' }}</strong></div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Gudang</label>
                <div><strong>{{ $item_journal->warehouse->warehouse_name ?? '-' }}</strong></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Produk</label>
                <div><strong>{{ $item_journal->product->product_name ?? '-' }} ({{ $item_journal->product->unit->unit_name ?? '-' }})</strong></div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Quantity</label>
                <div class="fs-5 fw-bold">{{ number_format($item_journal->quantity, 4, ',', '.') }}</div>
            </div>
            
            <div class="col-md-12 mb-3">
                <label class="form-label text-muted">Keterangan</label>
                <div class="p-3  rounded border">{{ $item_journal->description }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
