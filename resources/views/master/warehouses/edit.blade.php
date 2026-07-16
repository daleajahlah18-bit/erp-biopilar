@extends('layouts.app')
@section('title', 'Edit Gudang')
@section('page_title', 'Edit Gudang')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('master.warehouses.update', $warehouse) }}" method="POST">
            @csrf @method('PUT')
                        <div class="mb-3">
                <label class="form-label">Nama Gudang</label>
                <input type="text" name="warehouse_name" class="form-control" value="{{ old('warehouse_name', $warehouse->warehouse_name) }}" required>
            </div>            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $warehouse->description) }}</textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.warehouses.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection