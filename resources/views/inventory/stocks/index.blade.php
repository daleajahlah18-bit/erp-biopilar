@extends('layouts.app')
@section('title', 'Stok Produk')
@section('page_title', 'Stok Produk')
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('inventory.stocks.index') }}" method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Gudang</label>
                    <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (isset($warehouse_id) && $warehouse_id == $wh->id) ? 'selected' : '' }}>
                                {{ $wh->warehouse_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Cari Produk</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="search" class="form-control" placeholder="Kode / Nama Produk" value="{{ $search ?? '' }}">
                        <button class="btn-primary-custom" type="submit" style="white-space: nowrap;"><i class="bi bi-search"></i> Cari</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>@sortablelink('code', 'Kode')</th><th>@sortablelink('name', 'Nama Produk')</th><th>@sortablelink('gudang', 'Gudang')</th><th>@sortablelink('kuantitas', 'Kuantitas')</th></tr></thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr>
                        <td><strong>{{ $stock->product->product_code ?? '-' }}</strong></td>
                        <td>{{ $stock->product->product_name ?? '-' }}</td>
                        <td>{{ $stock->warehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ number_format($stock->quantity, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-secondary">Belum ada data stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $stocks->links() }}
        </div>
    </div>
</div>
@endsection