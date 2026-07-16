@extends('layouts.app')

@section('title', 'Produk')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Produk</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Master Produk')
@section('page_subtitle', 'Kelola data produk, bahan baku, dan BOM')

@section('header_actions')
<a href="{{ route('master.products.create') }}" class="btn-primary-custom">
    <i class="bi bi-plus-lg"></i> Tambah Produk
</a>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>@sortablelink('code', 'Kode')</th>
                        <th>@sortablelink('name', 'Nama Produk')</th>
                        <th>@sortablelink('tipe', 'Tipe')</th>
                        <th>@sortablelink('engineering_category', 'Eng. Category')</th>
                        <th>@sortablelink('satuan', 'Satuan')</th>
                        <th>@sortablelink('dibuat_oleh', 'Dibuat Oleh')</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td><strong>{{ $product->product_code }}</strong></td>
                        <td>{{ $product->product_name }}</td>
                        <td>
                            <span class="badge-status {{ $product->product_type == 'Bahan Baku' ? 'badge-draft' : ($product->product_type == 'Bahan Jadi' ? 'badge-approved' : 'badge-released') }}">
                                {{ $product->product_type }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $product->engineering_category == 'Civil' ? 'secondary' : ($product->engineering_category == 'Mechanical' ? 'info' : 'warning') }}">
                                {{ $product->engineering_category }}
                            </span>
                        </td>
                        <td>{{ $product->unit->unit_name ?? '-' }}</td>
                        <td>{{ $product->creator->name ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('master.products.show', $product) }}" class="btn btn-sm btn-outline-info me-1">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('master.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('master.products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); confirmDelete(() => this.closest('form').submit(), 'Hapus Data?', 'Yakin ingin menghapus produk ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">Belum ada data produk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer  border-top">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
