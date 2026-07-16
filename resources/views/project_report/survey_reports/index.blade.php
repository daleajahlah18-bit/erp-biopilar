@extends('layouts.app')
@section('title', 'Survey Report')
@section('page_title', 'Survey Report')

@section('header_actions')
    @can('survey_report.create')
        <a href="{{ route('survey-reports.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Survey Report</a>
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
                        <th>@sortablelink('survey_location', 'Survey Location')</th>
                        <th>@sortablelink('client', 'Client')</th>
                        <th>@sortablelink('created_at', 'Survey Date')</th>
                        <th>@sortablelink('surveyor', 'Surveyor')</th>
                        <th>@sortablelink('created_by', 'Created By')</th>
                        <th width="15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $index => $sr)
                    <tr>
                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                        <td class="align-middle"><strong>{{ $sr->report_number }}</strong></td>
                        <td class="align-middle">{{ $sr->survey_location }}</td>
                        <td class="align-middle">{{ $sr->client_name }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($sr->survey_date)->format('d M Y') }}</td>
                        <td class="align-middle">{{ $sr->surveyor }}</td>
                        <td class="align-middle">{{ $sr->creator->name ?? '-' }}</td>
                        <td class="text-center align-middle">
                            @can('survey_report.export')
                                <a href="{{ route('survey-reports.pdf', $sr->id) }}" target="_blank" class="btn btn-sm btn-secondary me-1" title="Print PDF"><i class="bi bi-printer"></i></a>
                            @endcan
                            @can('survey_report.edit')
                                <a href="{{ route('survey-reports.edit', $sr->id) }}" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('survey_report.delete')
                                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $sr->id }}" title="Delete"><i class="bi bi-trash"></i></button>
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
                    fetch(`/survey-reports/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            notifySuccess('Berhasil!', 'Survey Report berhasil dihapus.');
                            setTimeout(() => { location.reload(); }, 1000);
                        } else {
                            notifyError('Gagal!', data.message || 'Gagal menghapus Survey Report.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        notifyError('Kesalahan', 'Terjadi kesalahan sistem.');
                    });
                }, 'Hapus Survey Report?', 'Data yang dihapus tidak dapat dikembalikan.');
            }
        });
    });
</script>
@endpush
