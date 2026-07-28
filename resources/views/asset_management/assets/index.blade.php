@extends('layouts.app')
@section('title', 'Master Assets')
@section('page_title', 'Master Assets')

@section('header_actions')
<a href="{{ route('asset-management.assets.export') }}" class="btn btn-success" id="btnExport" title="Export current view to Excel">
    <i class="bi bi-file-excel"></i> Export Excel
</a>
<a href="{{ route('asset-management.assets.import.template') }}" class="btn btn-outline-success">
    <i class="bi bi-download"></i> Download Template
</a>
<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importModal">
    <i class="bi bi-upload"></i> Import From Excel
</button>
<a href="{{ route('asset-management.assets.create') }}" class="btn btn-primary">
    <i class="bi bi-plus"></i> New Asset
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>@sortablelink('asset_code', 'Asset Code')</th>
                        <th>@sortablelink('name', 'Name')</th>
                        <th>@sortablelink('category', 'Category')</th>
                        <th>@sortablelink('location', 'Location')</th>
                        <th>@sortablelink('acq._cost', 'Acq. Cost')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                    <tr>
                        <td>{{ $asset->asset_code }}</td>
                        <td>{{ $asset->asset_name }}</td>
                        <td>{{ $asset->category->category_name ?? '-' }}</td>
                        <td>{{ $asset->location }}</td>
                        <td>Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                        <td>
                            @if($asset->status == 'Active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Active</span>
                            @elseif($asset->status == 'Under Maintenance')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Maintenance</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $asset->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('asset-management.assets.show', $asset->id) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> Detail</a>
                                <form action="{{ route('asset-management.assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus asset ini? Data yang dihapus tidak dapat dikembalikan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('asset-management.assets.import.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Assets from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload Excel File (.xlsx, .xls)</label>
                        <input class="form-control" type="file" id="file" name="file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 mb-0">
                        <small>Pastikan Anda sudah mengunduh template terbaru melalui tombol <strong>Download Template</strong> dan mengisi data sesuai instruksi.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-upload"></i> Upload & Preview</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Ensure export button respects current DataTables/URL search filters if any
    document.getElementById('btnExport').addEventListener('click', function(e) {
        e.preventDefault();
        let currentUrl = window.location.href;
        let exportUrl = new URL(this.href);
        
        let searchParams = new URLSearchParams(window.location.search);
        searchParams.forEach((value, key) => {
            exportUrl.searchParams.append(key, value);
        });
        
        window.location.href = exportUrl.toString();
    });
</script>
@endpush
