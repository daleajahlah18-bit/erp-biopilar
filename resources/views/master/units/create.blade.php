@extends('layouts.app')
@section('title', 'Tambah Unit')
@section('page_title', 'Tambah Unit')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('master.units.store') }}" method="POST">
            @csrf
                        <div class="mb-3">
                <label class="form-label">Nama Unit</label>
                <input type="text" name="unit_name" class="form-control" value="{{ old('unit_name') }}" required>
            </div>            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.units.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection