@extends('layouts.app')

@section('title', 'Edit Produk')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.products.index') }}">Produk</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Edit Produk')

@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('master.products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Kode Produk</label>
                <input type="text" name="product_code" class="form-control @error('product_code') is-invalid @enderror" value="{{ old('product_code', $product->product_code) }}" required>
                @error('product_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror" value="{{ old('product_name', $product->product_name) }}" required>
                @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tipe Produk</label>
                <select name="product_type" class="form-select @error('product_type') is-invalid @enderror" required>
                    <option value="">-- Pilih Tipe --</option>
                    <option value="Bahan Baku" {{ old('product_type', $product->product_type) == 'Bahan Baku' ? 'selected' : '' }}>Bahan Baku</option>
                    <option value="Bahan Jadi" {{ old('product_type', $product->product_type) == 'Bahan Jadi' ? 'selected' : '' }}>Bahan Jadi</option>
                    <option value="Bill of Material" {{ old('product_type', $product->product_type) == 'Bill of Material' ? 'selected' : '' }}>Bill of Material</option>
                </select>
                @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Engineering Category</label>
                <select name="engineering_category" class="form-select @error('engineering_category') is-invalid @enderror" required>
                    <option value="">-- Pilih Category --</option>
                    <option value="Civil" {{ old('engineering_category', $product->engineering_category) == 'Civil' ? 'selected' : '' }}>Civil</option>
                    <option value="Mechanical" {{ old('engineering_category', $product->engineering_category) == 'Mechanical' ? 'selected' : '' }}>Mechanical</option>
                    <option value="Electrical" {{ old('engineering_category', $product->engineering_category) == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                </select>
                @error('engineering_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Satuan (Unit)</label>
                <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                    @endforeach
                </select>
                @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('master.products.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
