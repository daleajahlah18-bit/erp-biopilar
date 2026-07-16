@extends('layouts.app')
@section('title', 'Project Report')
@section('page_title', 'Project Report')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="#" method="GET" id="reportForm" class="row align-items-end">
            <div class="col-md-6">
                <label class="form-label font-weight-bold">Select Project</label>
                <select name="project_id" id="project_id" class="form-select select2">
                    <option value="">-- Choose a Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->project_name }} ({{ $p->client_name }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" id="btnViewReport" class="btn btn-primary w-100"><i class="bi bi-search"></i> View Report</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnViewReport').addEventListener('click', function() {
        const pid = document.getElementById('project_id').value;
        if(pid) {
            window.location.href = `/project-reports/${pid}`;
        } else {
            notifyError('Pemberitahuan', 'Silakan pilih project terlebih dahulu.');
        }
    });
});
</script>
@endpush
