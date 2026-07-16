@extends('layouts.app')
@section('title', 'Tambah Supplier')
@section('page_title', 'Tambah Supplier')
@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('master.suppliers.store') }}" method="POST">
            @csrf
                        <div class="mb-3">
                <label class="form-label">Nama Supplier</label>
                <input type="text" name="supplier_name" class="form-control" value="{{ old('supplier_name') }}" required>
            </div>            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="supplier_address" class="form-control" rows="3">{{ old('supplier_address') }}</textarea>
            </div>            <div class="mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="supplier_phone" class="form-control" value="{{ old('supplier_phone') }}" required>
            </div>            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="supplier_email" class="form-control" value="{{ old('supplier_email') }}" required>
            </div>            <div class="mb-3">
                <label class="form-label">Rekening Bank</label>
                <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}" required>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.suppliers.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection