@extends('layouts.app')
@section('title', 'Detail Stock Opname')
@section('page_title', 'Stock Opname')
@section('page_subtitle', $stock_opname->opname_number)

@section('header_actions')
<a href="{{ route('inventory.stock-opnames.index') }}" class="btn-outline-custom">Kembali</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h5 class="text-primary mb-3" style="font-weight: 600;">Header Opname</h5>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">No Opname</label>
                <div><strong>{{ $stock_opname->opname_number }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Tanggal Opname</label>
                <div><strong>{{ \Carbon\Carbon::parse($stock_opname->opname_date)->format('d/m/Y') }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Gudang</label>
                <div><strong>{{ $stock_opname->warehouse->warehouse_name ?? '-' }}</strong></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Dibuat Oleh</label>
                <div><strong>{{ $stock_opname->creator->name ?? 'System' }}</strong></div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label text-muted">Catatan</label>
                <div><strong>{{ $stock_opname->notes ?? '-' }}</strong></div>
            </div>
        </div>

        <hr class="my-4">
        
        <h5 class="text-primary mb-3" style="font-weight: 600;">Detail Perhitungan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th>Bahan Baku / Produk</th>
                        <th>Unit</th>
                        <th class="text-end">Stock Sistem</th>
                        <th class="text-end">Stock Fisik</th>
                        <th class="text-end">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stock_opname->details as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                            <td>{{ $detail->product->unit->unit_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($detail->system_stock, 4, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($detail->physical_stock, 4, ',', '.') }}</td>
                            <td class="text-end fw-bold {{ $detail->difference > 0 ? 'text-success' : ($detail->difference < 0 ? 'text-danger' : 'text-secondary') }}">
                                {{ $detail->difference > 0 ? '+' : '' }}{{ number_format($detail->difference, 4, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
