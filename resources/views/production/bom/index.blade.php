@extends('layouts.app')
@section('title', 'Data Bill of Material')
@section('page_title', 'Bill of Material')
@section('page_subtitle', 'Kelola Master Resep')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center  py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar BOM</h6>
        <a href="{{ route('production.bom.create') }}" class="btn-primary-custom text-decoration-none py-2 px-3">
            <i class="bi bi-plus-lg"></i> Buat BOM
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead class="">
                    <tr>
                        <th>No</th>
                        <th>No BOM</th>
                        <th>@sortablelink('name', 'Nama BOM')</th>
                        <th>@sortablelink('produk_jadi', 'Produk Jadi')</th>
                        <th class="text-end">@sortablelink('total', 'Total HPP (Rp)')</th>
                        <th>@sortablelink('catatan', 'Catatan')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boms as $index => $bom)
                    <tr>
                        <td>{{ $boms->firstItem() + $index }}</td>
                        <td><span class="badge bg-secondary">{{ $bom->bom_number }}</span></td>
                        <td class="fw-bold">{{ $bom->bom_name }}</td>
                        <td>{{ $bom->product ? $bom->product->product_name : '-' }}</td>
                        <td class="text-end text-primary fw-bold">{{ number_format($bom->total_hpp, 2, ',', '.') }}</td>
                        <td>{{ Str::limit($bom->notes, 30) ?: '-' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('production.bom.show', $bom->id) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('production.bom.edit', $bom->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('production.bom.destroy', $bom->id) }}" method="POST" class="d-inline" onsubmit="event.preventDefault(); confirmDelete(() => this.submit(), 'Hapus Data?', 'Apakah Anda yakin ingin menghapus BOM ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data Bill of Material.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $boms->links() }}
        </div>
    </div>
</div>
@endsection