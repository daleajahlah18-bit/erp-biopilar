<?php

mkdir('resources/views/master/customers', 0777, true);

// 1. Index
file_put_contents('resources/views/master/customers/index.blade.php', '
@extends("layouts.app")
@section("title", "Master Customer")
@section("page_title", "Data Customer")
@section("page_subtitle", "Manajemen Pelanggan")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Customer</h5>
        <a href="{{ route("master.customers.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Tambah Customer
        </a>
    </div>
    <div class="card-body">
        @if(session("success"))
            <div class="alert alert-success">{{ session("success") }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Customer</th>
                        <th>PIC</th>
                        <th>Telepon</th>
                        <th>Terms (Hari)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $item)
                        <tr>
                            <td>{{ $item->customer_code }}</td>
                            <td>{{ $item->customer_name }}</td>
                            <td>{{ $item->customer_pic }}</td>
                            <td>{{ $item->customer_phone }}</td>
                            <td>{{ $item->payment_terms }} Hari</td>
                            <td>
                                @if($item->status == "Active")
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route("master.customers.edit", $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route("master.customers.destroy", $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm(\'Hapus data ini?\')">
                                    @csrf @method("DELETE")
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada data customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
');

// 2. Create
file_put_contents('resources/views/master/customers/create.blade.php', '
@extends("layouts.app")
@section("title", "Tambah Customer")
@section("page_title", "Customer")
@section("page_subtitle", "Tambah Data Baru")

@section("content")
<div class="card">
    <div class="card-body">
        <form action="{{ route("master.customers.store") }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Kode Customer *</label>
                    <input type="text" name="customer_code" class="form-control" required value="{{ old("customer_code") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Nama Customer *</label>
                    <input type="text" name="customer_name" class="form-control" required value="{{ old("customer_name") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Nama PIC</label>
                    <input type="text" name="customer_pic" class="form-control" value="{{ old("customer_pic") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>No Telepon</label>
                    <input type="text" name="customer_phone" class="form-control" value="{{ old("customer_phone") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="customer_email" class="form-control" value="{{ old("customer_email") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Term of Payment (Hari) *</label>
                    <input type="number" name="payment_terms" class="form-control" required min="0" value="{{ old("payment_terms", 0) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Alamat Lengkap</label>
                    <textarea name="customer_address" class="form-control" rows="3">{{ old("customer_address") }}</textarea>
                </div>
            </div>
            <div class="text-end">
                <a href="{{ route("master.customers.index") }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
');

// 3. Edit
file_put_contents('resources/views/master/customers/edit.blade.php', '
@extends("layouts.app")
@section("title", "Edit Customer")
@section("page_title", "Customer")
@section("page_subtitle", "Edit Data")

@section("content")
<div class="card">
    <div class="card-body">
        <form action="{{ route("master.customers.update", $customer->id) }}" method="POST">
            @csrf @method("PUT")
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Kode Customer *</label>
                    <input type="text" name="customer_code" class="form-control" required value="{{ old("customer_code", $customer->customer_code) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Nama Customer *</label>
                    <input type="text" name="customer_name" class="form-control" required value="{{ old("customer_name", $customer->customer_name) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Nama PIC</label>
                    <input type="text" name="customer_pic" class="form-control" value="{{ old("customer_pic", $customer->customer_pic) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>No Telepon</label>
                    <input type="text" name="customer_phone" class="form-control" value="{{ old("customer_phone", $customer->customer_phone) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="customer_email" class="form-control" value="{{ old("customer_email", $customer->customer_email) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Term of Payment (Hari) *</label>
                    <input type="number" name="payment_terms" class="form-control" required min="0" value="{{ old("payment_terms", $customer->payment_terms) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Active" {{ $customer->status == "Active" ? "selected" : "" }}>Active</option>
                        <option value="Inactive" {{ $customer->status == "Inactive" ? "selected" : "" }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Alamat Lengkap</label>
                    <textarea name="customer_address" class="form-control" rows="3">{{ old("customer_address", $customer->customer_address) }}</textarea>
                </div>
            </div>
            <div class="text-end">
                <a href="{{ route("master.customers.index") }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
');
