@extends('layouts.app')
@section('page_title', 'Edit Transfer Stok')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="#">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Update Data</label>
                <input type="text" class="form-control" name="data">
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-outline-custom">Batal</button>
                <button type="submit" class="btn-primary-custom">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection