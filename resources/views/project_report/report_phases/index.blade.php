@extends('layouts.app')
@section('title', 'Report Phase')
@section('page_title', 'Report Phase / BAPP')

@section('header_actions')
    @can('report_phase.create')
        <a href="{{ route('report-phases.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Report Phase</a>
    @endcan
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>@sortablelink('report_number', 'Report Number')</th>
                        <th>@sortablelink('project_name', 'Project Name')</th>
                        <th>@sortablelink('client', 'Client')</th>
                        <th>@sortablelink('created_at', 'Document Date')</th>
                        <th>@sortablelink('progress', 'Progress')</th>
                        <th>@sortablelink('created_by', 'Created By')</th>
                        <th width="15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportPhases as $index => $rp)
                    <tr>
                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                        <td class="align-middle"><strong>{{ $rp->report_number }}</strong></td>
                        <td class="align-middle">{{ $rp->project->project_name ?? '-' }}</td>
                        <td class="align-middle">{{ $rp->project->client_name ?? '-' }}</td>
                        <td class="align-middle">{{ $rp->document_date ? $rp->document_date->format('d M Y') : '-' }}</td>
                        <td class="align-middle text-center"><span class="badge bg-info text-dark">{{ $rp->progress_percentage }}%</span></td>
                        <td class="align-middle">{{ $rp->creator->name ?? '-' }}</td>
                        <td class="text-center align-middle">
                            @can('report_phase.export')
                                <a href="{{ route('report-phases.pdf', $rp->id) }}" target="_blank" class="btn btn-sm btn-secondary me-1" title="Print PDF"><i class="bi bi-printer"></i></a>
                            @endcan
                            @can('report_phase.edit')
                                <a href="{{ route('report-phases.edit', $rp->id) }}" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('report_phase.delete')
                                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $rp->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#dataTable').DataTable({
                "pageLength": 25,
                "ordering": true,
                "order": [[ 0, 'asc' ]]
            });
        }
        
        document.body.addEventListener('click', function(e) {
            const btnDelete = e.target.closest('.btn-delete');
            if (btnDelete) {
                const id = btnDelete.getAttribute('data-id');
                confirmDelete(() => {
                    fetch(`/report-phases/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            notifySuccess('Berhasil!', 'Report Phase berhasil dihapus.');
                            setTimeout(() => { location.reload(); }, 1000);
                        } else {
                            notifyError('Gagal!', data.message || 'Gagal menghapus Report Phase.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        notifyError('Kesalahan', 'Terjadi kesalahan sistem.');
                    });
                }, 'Hapus Report Phase?', 'Data yang dihapus tidak dapat dikembalikan.');
            }
        });
    });
</script>
@endpush
