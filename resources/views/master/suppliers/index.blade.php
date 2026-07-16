@extends('layouts.app')
@section('title', 'Supplier')
@section('page_title', 'Master Supplier')
@section('header_actions')
<a href="{{ route('master.suppliers.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Tambah Supplier</a>
@endsection
@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>@sortablelink('name', 'Nama Supplier')</th><th>@sortablelink('alamat', 'Alamat')</th><th>No. Telepon</th><th>@sortablelink('email', 'Email')</th><th>@sortablelink('rekening_bank', 'Rekening Bank')</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->supplier_name }}</td><td>{{ $supplier->supplier_address }}</td><td>{{ $supplier->supplier_phone }}</td><td>{{ $supplier->supplier_email }}</td><td>{{ $supplier->bank_account }}</td>
                        <td class="text-end">
                            <a href="{{ route('master.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('master.suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); confirmDelete(() => this.closest('form').submit(), 'Hapus Data?', 'Yakin hapus?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($suppliers->hasPages()) <div class="card-footer  border-top">{{ $suppliers->links() }}</div> @endif
</div>
@endsection