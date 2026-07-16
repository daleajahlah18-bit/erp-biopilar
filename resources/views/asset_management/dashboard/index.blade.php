@extends('layouts.app')
@section('title', 'Asset Dashboard')
@section('page_title', 'Asset Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
            <div>
                <strong>Dynamic Depreciation Engine Active</strong><br>
                All asset values shown below and across the system are calculated dynamically based on today's date ({{ \Carbon\Carbon::now()->format('d F Y') }}). There is no need to run monthly depreciation manually.
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Total Assets</h6>
                <h4 class="mb-0 fw-bold">{{ $totalAssets }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Acquisition Cost</h6>
                <h4 class="mb-0 fw-bold">Rp {{ number_format($totalCost, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card h-100 border-primary">
            <div class="card-body">
                <h6 class="text-primary">Commercial Book Value</h6>
                <h4 class="mb-0 fw-bold text-primary">Rp {{ number_format($commercialBookValue, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card h-100 border-success">
            <div class="card-body">
                <h6 class="text-success">Fiscal Book Value</h6>
                <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($fiscalBookValue, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection
