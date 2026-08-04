@extends('layouts.app')

@section('title', 'Rencana Anggaran Biaya (RAB)')
@section('page_title', 'Rencana Anggaran Biaya (RAB)')
@section('page_subtitle', 'Kelola semua RAB Proyek')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">RAB</li>
    </ol>
</nav>
@endsection

@section('header_actions')
<div class="d-flex gap-2">
    @can('rab.create')
    <a href="{{ route('rabs.create') }}" class="btn-primary-custom text-decoration-none">
        <i class="bi bi-plus-lg"></i> Create RAB
    </a>
    @endcan
    @can('rab.import')
    <a href="{{ route('rabs.excel.template') }}" class="btn-outline-custom text-decoration-none" title="Download Template">
        <i class="bi bi-file-earmark-excel"></i> Template
    </a>
    <button type="button" class="btn-outline-custom text-decoration-none" data-bs-toggle="modal" data-bs-target="#importModal" title="Import RAB">
        <i class="bi bi-upload"></i> Import
    </button>
    @endcan
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-custom">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Project</th>
                        <th>RAB Name</th>
                        <th>@sortablelink('total_amount', 'Total Amount')</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th class="text-end" width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rabs as $index => $rab)
                    <tr>
                        <td>{{ $rabs->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold">{{ $rab->project->project_name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary">{{ $rab->rab_name }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">Rp {{ number_format($rab->total_amount, 2, ',', '.') }}</div>
                        </td>
                        <td>
                            <span class="badge-status badge-{{ strtolower($rab->status) }}">{{ $rab->status }}</span>
                        </td>
                        <td>
                            {{ $rab->created_at->format('d M Y') }}<br>
                            <small class="text-muted">{{ $rab->creator->name ?? 'System' }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                @can('rab.view')
                                <a href="{{ route('rabs.show', $rab->id) }}" class="btn btn-sm btn-light text-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endcan
                                
                                @can('rab.export')
                                <a href="{{ route('rabs.export', $rab->id) }}" class="btn btn-sm btn-light text-success" title="Export to Excel">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                </a>
                                @endcan

                                @can('rab.edit')
                                <a href="{{ route('rabs.edit', $rab->id) }}" class="btn btn-sm btn-light text-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan

                                @can('rab.delete')
                                <form action="{{ route('rabs.destroy', $rab->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-light text-danger btn-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <div class="mb-3">
                                <i class="bi bi-inbox fs-1"></i>
                            </div>
                            No RABs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($rabs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $rabs->links() }}
    </div>
    @endif
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import RAB Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rabs.excel.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select select2" required>
                            <option value="">Select Project...</option>
                            @foreach(\App\Models\Project::orderBy('project_name')->get() as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RAB Name</label>
                        <input type="text" name="rab_name" class="form-control" required placeholder="e.g. RAB Tahap 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excel File</label>
                        <input type="file" name="file" class="form-control" required accept=".xlsx, .xls">
                        <small class="text-muted">Ensure you use the provided template format.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom" id="btnPreviewImport">
                        <i class="bi bi-search"></i> Preview Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .select2-container { width: 100% !important; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#importModal')
        });

        $('.btn-delete').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            confirmDelete(() => {
                form.submit();
            });
        });

        $('#importModal form').on('submit', function() {
            let btn = $('#btnPreviewImport');
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
        });
    });
</script>
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        notifySuccess('Berhasil!', '{{ session('success') }}');
    });
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        notifyError('Terjadi Kesalahan', '{{ session('error') }}');
    });
</script>
@endif
@endpush
