@extends('layouts.app')

@section('title', 'Detail Produk')
@section('page_title', 'Master Produk')
@section('page_subtitle', $product->product_code)

@section('header_actions')
<a href="{{ route('master.products.index') }}" class="btn-outline-custom">Kembali</a>
<a href="{{ route('master.products.edit', $product) }}" class="btn-primary-custom ms-2">Edit</a>
@endsection

@section('content')
<div class="card mb-4" style="max-width: 800px;">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Kode Produk</label>
                <div><strong>{{ $product->product_code }}</strong></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Nama Produk</label>
                <div><strong>{{ $product->product_name }}</strong></div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Tipe Produk</label>
                <div>
                    <span class="badge-status {{ $product->product_type == 'Bahan Baku' ? 'badge-draft' : ($product->product_type == 'Bahan Jadi' ? 'badge-approved' : 'badge-released') }}">
                        {{ $product->product_type }}
                    </span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Engineering Category</label>
                <div>
                    <span class="badge bg-{{ $product->engineering_category == 'Civil' ? 'secondary' : ($product->engineering_category == 'Mechanical' ? 'info' : 'warning') }}">
                        {{ $product->engineering_category }}
                    </span>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Satuan</label>
                <div><strong>{{ $product->unit->unit_name ?? '-' }}</strong></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Dibuat Oleh</label>
                <div><strong>{{ $product->creator->name ?? '-' }}</strong></div>
            </div>
            
            <div class="col-md-12 mb-3">
                <label class="form-label text-muted">Deskripsi</label>
                <div class="p-3  rounded border">{{ $product->description ?? 'Tidak ada deskripsi' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
