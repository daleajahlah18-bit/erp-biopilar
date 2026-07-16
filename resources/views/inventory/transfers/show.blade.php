@extends('layouts.app')
@section('title', 'Detail Transfer Stok')
@section('page_title', 'Transfer Stok')
@section('page_subtitle', $inventory_transfer->transfer_number)

@section('header_actions')
<a href="{{ route('inventory.transfers.index') }}" class="btn-outline-custom">Kembali</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h5 class="text-primary mb-3" style="font-weight: 600;">Header Transfer</h5>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">No Transfer</label>
                <div><strong>{{ $inventory_transfer->transfer_number }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Tanggal Transfer</label>
                <div><strong>{{ \Carbon\Carbon::parse($inventory_transfer->transfer_date)->format('d/m/Y') }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Gudang Asal</label>
                <div><strong>{{ $inventory_transfer->sourceWarehouse->warehouse_name ?? '-' }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Gudang Tujuan</label>
                <div><strong>{{ $inventory_transfer->destinationWarehouse->warehouse_name ?? '-' }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Dibuat Oleh</label>
                <div><strong>{{ $inventory_transfer->creator->name ?? 'System' }}</strong></div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label text-muted">Catatan</label>
                <div><strong>{{ $inventory_transfer->notes ?? '-' }}</strong></div>
            </div>
        </div>

        <hr class="my-4">
        
        <h5 class="text-primary mb-3" style="font-weight: 600;">Detail Produk</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th>Bahan Baku / Produk</th>
                        <th>Unit</th>
                        <th class="text-end">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventory_transfer->details as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                            <td>{{ $detail->product->unit->unit_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($detail->quantity, 4, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection