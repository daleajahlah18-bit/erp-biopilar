@extends('layouts.app')

@section('title', 'Preview Import Products')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.products.index') }}">Produk</a></li>
        <li class="breadcrumb-item active">Preview Import</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Preview Import Data')
@section('page_subtitle', 'Review your data before committing to the database.')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="card-title">Total Rows</h6>
                <h2 class="mb-0">{{ $sessionData['total_rows'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6 class="card-title">Ready / Valid</h6>
                <h2 class="mb-0">{{ $sessionData['valid_rows'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h6 class="card-title">Failed / Invalid</h6>
                <h2 class="mb-0">{{ $sessionData['invalid_rows'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Error Summary</h6>
                @if(count($sessionData['error_summary']) > 0)
                    <ul class="list-unstyled mb-0" style="max-height: 80px; overflow-y: auto;">
                        @foreach($sessionData['error_summary'] as $errorMsg => $count)
                            <li class="text-danger small"><i class="bi bi-x-circle me-1"></i> {{ $errorMsg }}: <strong>{{ $count }}</strong></li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-success small mt-2"><i class="bi bi-check-circle me-1"></i> All data is valid!</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Preview</h5>
        <div class="d-flex gap-2">
            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Status</option>
                <option value="Ready">Ready</option>
                <option value="Failed">Failed</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover table-bordered mb-0" id="previewTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Status</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Error Validation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessionData['data'] as $row)
                    <tr class="status-row {{ $row['status'] == 'Ready' ? 'table-success' : 'table-danger' }}" data-status="{{ $row['status'] }}">
                        <td>
                            @if($row['status'] == 'Ready')
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Ready</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Failed</span>
                            @endif
                        </td>
                        <td>{{ $row['product_code'] }}</td>
                        <td>{{ $row['product_name'] }}</td>
                        <td>{{ $row['product_type_raw'] }}</td>
                        <td>{{ $row['engineering_category_raw'] }}</td>
                        <td>{{ $row['unit_raw'] }}</td>
                        <td class="text-danger fw-bold">{{ $row['errors'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('master.products.index') }}" class="btn btn-secondary">Cancel Import</a>
        
        <form action="{{ route('master.products.import.process') }}" method="POST" id="importForm">
            @csrf
            <input type="hidden" name="import_id" value="{{ $sessionData['import_id'] }}">
            <button type="submit" class="btn btn-primary" id="btnImport" {{ $sessionData['valid_rows'] == 0 ? 'disabled' : '' }}>
                <i class="bi bi-cloud-arrow-up"></i> Confirm Import ({{ $sessionData['valid_rows'] }} rows)
            </button>
        </form>
    </div>
</div>

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
        
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing... please wait';
        });
    });
</script>
@endsection
