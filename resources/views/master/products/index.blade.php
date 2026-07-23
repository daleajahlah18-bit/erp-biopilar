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
<a href="{{ route('master.products.import.template') }}" class="btn btn-outline-secondary me-2">
    <i class="bi bi-download"></i> Download Template
</a>
<button type="button" class="btn btn-info text-white me-2" data-bs-toggle="modal" data-bs-target="#importModal">
    <i class="bi bi-file-earmark-excel"></i> Import From Excel
</button>
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

@if(session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}'
        });
    });
</script>
@endif

@if($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            html: `
                <ul class="text-start mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            `
        });
    });
</script>
@endif

@if($recentImports && $recentImports->count() > 0)
<div class="accordion mb-4" id="importHistoryAccordion">
    <div class="accordion-item shadow-sm border-0">
        <h2 class="accordion-header" id="headingImportHistory">
            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImportHistory" aria-expanded="false" aria-controls="collapseImportHistory">
                <i class="bi bi-clock-history me-2"></i> Recent Imports History
            </button>
        </h2>
        <div id="collapseImportHistory" class="accordion-collapse collapse" aria-labelledby="headingImportHistory" data-bs-parent="#importHistoryAccordion">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentImports as $import)
                            <tr>
                                <td>{{ $import->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $import->causer ? $import->causer->name : 'System' }}</td>
                                <td>{!! nl2br(e($import->description)) !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('master.products.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Kode, Nama, Tipe..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
            </div>
            @if(request()->filled('search'))
            <div class="col-md-2">
                <a href="{{ route('master.products.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
            @endif
        </form>
    </div>
</div>

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
    <div class="card-footer border-top">
        {{ $products->links() }}
    </div>
    @endif
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('master.products.import.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Products From Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Excel File (.xlsx, .xls)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <small class="text-muted">Max file size: 10MB. Please use the downloaded template format.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload & Preview</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(session('import_success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let errorButtonHtml = '';
        @if(session('import_success')['failed'] > 0)
            errorButtonHtml = `<a href="{{ route('master.products.import.errors', session('import_success')['import_id']) }}" class="btn btn-danger w-100 mt-3" style="color:white; text-decoration:none;"><i class="bi bi-download"></i> Download Error Report</a>`;
        @endif

        Swal.fire({
            title: 'Import Completed',
            icon: '{{ session('import_success')['failed'] > 0 ? 'warning' : 'success' }}',
            html: `
                <ul class="list-group mb-3 text-start">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total File Rows
                        <span class="badge bg-secondary rounded-pill">{{ session('import_success')['total'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Imported Successfully
                        <span class="badge bg-success rounded-pill">{{ session('import_success')['imported'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Skipped
                        <span class="badge bg-warning rounded-pill">{{ session('import_success')['skipped'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Failed
                        <span class="badge bg-danger rounded-pill">{{ session('import_success')['failed'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Duration
                        <span class="badge bg-info rounded-pill">{{ session('import_success')['duration'] }} Seconds</span>
                    </li>
                </ul>
                ${errorButtonHtml}
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#3085d6',
            allowOutsideClick: false
        }).then((result) => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Proses import telah selesai.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        });
    });
</script>
@endif

@endsection
