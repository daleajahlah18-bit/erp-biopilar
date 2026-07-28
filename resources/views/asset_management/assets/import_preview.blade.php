@extends('layouts.app')

@section('title', 'Preview Import Assets')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('asset-management.assets.index') }}">Assets</a></li>
        <li class="breadcrumb-item active">Preview Import</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Preview Import Data')
@section('page_subtitle', 'Review your data before committing to the database.')

@section('content')
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card h-100 border-start border-primary border-4 shadow-sm">
            <div class="card-body p-3">
                <h6 class="card-title text-muted mb-1" style="font-size: 0.8rem;">Total Rows</h6>
                <h3 class="mb-0 text-primary">{{ $sessionData['total_rows'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-success border-4 shadow-sm">
            <div class="card-body p-3">
                <h6 class="card-title text-muted mb-1" style="font-size: 0.8rem;">Create (New)</h6>
                <h3 class="mb-0 text-success">{{ $sessionData['create_count'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-info border-4 shadow-sm">
            <div class="card-body p-3">
                <h6 class="card-title text-muted mb-1" style="font-size: 0.8rem;">Update (Existing)</h6>
                <h3 class="mb-0 text-info">{{ $sessionData['update_count'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-danger border-4 shadow-sm">
            <div class="card-body p-3">
                <h6 class="card-title text-muted mb-1" style="font-size: 0.8rem;">Failed / Invalid</h6>
                <h3 class="mb-0 text-danger">{{ $sessionData['invalid_rows'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Preview</h5>
        <div class="d-flex gap-2">
            @if($sessionData['invalid_rows'] > 0)
            <a href="{{ route('asset-management.assets.import.errors', $sessionData['import_id']) }}" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-download"></i> Download Error Report
            </a>
            @endif
            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Actions</option>
                <option value="Create">Create</option>
                <option value="Update">Update</option>
                <option value="Failed">Failed</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover table-bordered mb-0" id="previewTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Action</th>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Department</th>
                        <th>PIC</th>
                        <th>Acq. Cost</th>
                        <th>Error / Suggested Fix</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessionData['data'] as $row)
                    @php
                        $rowClass = '';
                        $p = $row['parsed'];
                        if ($row['status'] == 'Failed') $rowClass = 'table-danger';
                        elseif ($row['action'] == 'Update') $rowClass = 'table-info';
                    @endphp
                    <tr class="status-row {{ $rowClass }}" data-status="{{ $row['status'] == 'Failed' ? 'Failed' : $row['action'] }}">
                        <td>
                            @if($row['status'] == 'Failed')
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Failed</span>
                            @elseif($row['action'] == 'Update')
                                <span class="badge bg-info text-dark"><i class="bi bi-pencil-square"></i> Update</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-plus-circle"></i> Create</span>
                            @endif
                        </td>
                        <td>{{ $p['asset_code'] ?? '-' }}</td>
                        <td>{{ $p['asset_name'] }}</td>
                        <td>{{ $row['raw']['category'] ?? '' }}</td>
                        <td>{{ $p['department'] }}</td>
                        <td>{{ $p['responsible_person'] }}</td>
                        <td>{{ number_format((float)($p['acquisition_cost'] ?? 0)) }}</td>
                        <td>
                            @if($row['status'] == 'Failed')
                                <div class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> {{ $row['errors'] }}</div>
                                <div class="text-primary small"><i class="bi bi-lightbulb"></i> {{ $row['suggested_fix'] }}</div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('asset-management.assets.index') }}" class="btn btn-secondary">Cancel Import</a>
        
        <form action="{{ route('asset-management.assets.import.process') }}" method="POST" id="importForm">
            @csrf
            <input type="hidden" name="import_id" value="{{ $sessionData['import_id'] }}">
            <button type="submit" class="btn btn-primary" id="btnImport" {{ ($sessionData['create_count'] + $sessionData['update_count']) == 0 ? 'disabled' : '' }}>
                <i class="bi bi-cloud-arrow-up"></i> Confirm Import ({{ $sessionData['create_count'] + $sessionData['update_count'] }} rows)
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Status Filter
        const filter = document.getElementById('statusFilter');
        const rows = document.querySelectorAll('.status-row');
        
        filter.addEventListener('change', function() {
            const status = this.value;
            rows.forEach(row => {
                if(status === '' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Form Submit Loading
        const form = document.getElementById('importForm');
        const btn = document.getElementById('btnImport');
        
        if (form) {
            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing... please wait';
            });
        }
    });
</script>
@endpush
@endsection
