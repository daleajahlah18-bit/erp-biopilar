@extends('layouts.app')
@section('title', 'Buat Item Journal Manual')
@section('page_title', 'Item Journal Manual')
@section('page_subtitle', 'Penyesuaian stok masuk / keluar')

@section('content')
<div class="card mb-4" style="max-width: 800px;">
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inventory.item-journals.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Tanggal Transaksi</label>
                <input type="date" name="transaction_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Gudang</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- Pilih Gudang --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Produk</label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->product_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Transaksi</label>
                    <select name="transaction_type" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="STOCK_IN">STOCK_IN (Barang Masuk)</option>
                        <option value="STOCK_OUT">STOCK_OUT (Barang Keluar)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Keterangan</label>
                <textarea name="description" class="form-control" rows="3" required placeholder="Wajib diisi..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.item-journals.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan Jurnal</button>
            </div>
        </form>
    </div>
</div>
@endsection
