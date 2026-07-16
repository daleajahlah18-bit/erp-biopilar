
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
