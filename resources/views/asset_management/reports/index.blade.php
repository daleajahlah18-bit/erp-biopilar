@extends('layouts.app')
@section('title', 'Asset Reports')
@section('page_title', 'Asset Reports')

@section('content')
<div class="card">
    <div class="card-body">
        <h5>Available Reports</h5>
        <ul>
            <li>Asset Register</li>
            <li>Commercial Depreciation Schedule</li>
            <li>Fiscal Depreciation Schedule</li>
            <li>Maintenance History</li>
        </ul>
        <a href="{{ route('asset-management.reports.export-pdf', ['type' => 'asset_register']) }}" class="btn btn-danger mt-3">
            <i class="bi bi-file-pdf"></i> Export Asset Register (PDF)
        </a>
    </div>
</div>
@endsection
