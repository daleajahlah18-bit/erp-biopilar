@extends('layouts.app')
@section('title', 'Edit Unit')
@section('page_title', 'Edit Unit')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('master.units.update', $unit) }}" method="POST">
            @csrf @method('PUT')
                        <div class="mb-3">
                <label class="form-label">Nama Unit</label>
                <input type="text" name="unit_name" class="form-control" value="{{ old('unit_name', $unit->unit_name) }}" required>
            </div>            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $unit->description) }}</textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.units.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection