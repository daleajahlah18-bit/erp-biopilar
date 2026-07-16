@extends('layouts.app')
@section('title', 'Project Report: ' . $projectInfo->project_name)
@section('page_title', 'Project Report')

@section('content')

@php
    $terms = $projectInfo->projectPaymentTerms;
    $hasSchedule = $terms && $terms->count() > 0;
    $projectVal = $projectInfo->project_value;
    $totTermin = 0;
    $totPaid = 0;
    $totOuts = 0;
    if($hasSchedule) {
        foreach($terms as $term) {
            $totTermin += $term->nominal;
            $totPaid += $term->total_paid;
            $totOuts += $term->remaining_amount;
        }
    }
    $progPct = $projectVal > 0 ? ($totPaid / $projectVal) * 100 : 0;
@endphp

<!-- Header Actions -->
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between">
        <a href="{{ route('project-reports.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('project-reports.pdf', $projectInfo->id) }}" class="btn btn-danger btn-sm shadow-sm">
            <i class="bi bi-file-pdf"></i> Download PDF
        </a>
    </div>
</div>

<!-- Project Information -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white py-2">
        <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Project Information</h6>
    </div>
    <div class="card-body py-2">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td width="35%" class="text-muted" style="font-size:14px; padding-left:0;">Project Name</td><td style="font-size:14px;" class="fw-bold">: {{ $projectInfo->project_name }}</td></tr>
                    <tr><td class="text-muted" style="font-size:14px; padding-left:0;">Client Name</td><td style="font-size:14px;">: {{ $projectInfo->client_name }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td width="35%" class="text-muted" style="font-size:14px; padding-left:0;">PR Date</td><td style="font-size:14px;">: {{ $projectInfo->client_po_date ? $projectInfo->client_po_date->format('d F Y') : '-' }}</td></tr>
                    <tr><td class="text-muted" style="font-size:14px; padding-left:0;">Status</td><td>: 
                        <span class="badge bg-{{ $projectInfo->project_status == 'Completed' ? 'success' : 'primary' }}">{{ $projectInfo->project_status }}</span>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary KPIs -->
<div class="row mb-3 g-2">
    <div class="col-6 col-md">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Project Value</span>
                <span class="fw-bold text-primary" style="font-size: 15px;">Rp {{ number_format($projectVal, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Total Termin</span>
                <span class="fw-bold text-info" style="font-size: 15px;">Rp {{ number_format($totTermin, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Total Paid</span>
                <span class="fw-bold text-success" style="font-size: 15px;">Rp {{ number_format($totPaid, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Outstanding</span>
                <span class="fw-bold text-danger" style="font-size: 15px;">Rp {{ number_format($totOuts, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Payment Progress</span>
                <span class="fw-bold" style="font-size: 15px;">{{ number_format($progPct, 2) }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3 g-2">
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Total HPP</span>
                <span class="fw-bold text-danger" style="font-size: 15px;">Rp {{ number_format($financialSummary['total_hpp'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Margin</span>
                <span class="fw-bold text-success" style="font-size: 15px;">Rp {{ number_format($financialSummary['margin'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body p-2 d-flex flex-column justify-content-center text-center">
                <span class="text-muted mb-1" style="font-size: 13px;">Margin (%)</span>
                <span class="fw-bold {{ $financialSummary['margin_percentage'] < 0 ? 'text-danger' : 'text-success' }}" style="font-size: 15px;">
                    {{ number_format($financialSummary['margin_percentage'], 2) }}%
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Payment Progress Table -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapsePayment" style="cursor: pointer;">
        <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Payment Progress Schedule</h6>
        <i class="bi bi-chevron-down text-primary"></i>
    </div>
    <div class="collapse show" id="collapsePayment">
        <div class="card-body p-0">
            @if(!$hasSchedule)
                <div class="alert alert-info text-center m-3 py-2">No Payment Schedule has been configured for this Project.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 14px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>TOP</th>
                                <th class="text-center">%</th>
                                <th>Termin</th>
                                <th class="text-end">Nominal (Rp)</th>
                                <th class="text-end">Sudah Dibayar (Rp)</th>
                                <th class="text-end">Sisa Tagihan (Rp)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($terms as $index => $term)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $term->term_name }}</td>
                                <td class="text-center">{{ number_format($term->percentage, 2) }}%</td>
                                <td>{{ $term->termin_days }} Days</td>
                                <td class="text-end">{{ number_format($term->nominal, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($term->total_paid, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($term->remaining_amount, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $term->status == 'Paid' ? 'success' : ($term->status == 'Partial' ? 'warning' : 'danger') }}">
                                        {{ $term->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Additional Project Information Accordions -->
<div class="accordion mb-3" id="projectDetailsAccordion">
    
    <!-- Sales Order History -->
    <div class="accordion-item shadow-sm border-0 mb-2">
        <h2 class="accordion-header" id="headingSales">
            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSales" aria-expanded="false" aria-controls="collapseSales">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Sales Order History</h6>
            </button>
        </h2>
        <div id="collapseSales" class="accordion-collapse collapse" aria-labelledby="headingSales">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 14px;">
                        <thead class="bg-light">
                            <tr>
                                <th>SO Number</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Creator</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesHistory as $sale)
                            <tr>
                                <td>{{ $sale->sales_order_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sales_order_date)->format('d M Y') }}</td>
                                <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                <td><span class="badge bg-info">{{ $sale->status }}</span></td>
                                <td>{{ $sale->creator->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-2 text-muted">No Sales Orders found for this project.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Purchased Summary -->
    <div class="accordion-item shadow-sm border-0 mb-2">
        <h2 class="accordion-header" id="headingPurchased">
            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePurchased" aria-expanded="false" aria-controls="collapsePurchased">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Material Purchased Summary</h6>
            </button>
        </h2>
        <div id="collapsePurchased" class="accordion-collapse collapse" aria-labelledby="headingPurchased">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 14px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Product Code</th>
                                <th>Qty Purchased</th>
                                <th>Unit</th>
                                <th>Total Cost (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasedMaterials ?? [] as $purchased)
                            <tr>
                                <td>{{ $purchased['product_name'] }}</td>
                                <td>{{ $purchased['product_code'] }}</td>
                                <td>{{ number_format($purchased['quantity'], 2, ',', '.') }}</td>
                                <td>{{ $purchased['unit'] }}</td>
                                <td>{{ number_format($purchased['total_cost'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-2 text-muted">No Material Purchased recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Usage Summary -->
    <div class="accordion-item shadow-sm border-0 mb-2">
        <h2 class="accordion-header" id="headingMaterial">
            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaterial" aria-expanded="false" aria-controls="collapseMaterial">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Material Usage Summary</h6>
            </button>
        </h2>
        <div id="collapseMaterial" class="accordion-collapse collapse" aria-labelledby="headingMaterial">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 14px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Product Code</th>
                                <th>Qty Used</th>
                                <th>Unit</th>
                                <th>Material Cost (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materialUsage as $usage)
                            <tr>
                                <td>{{ $usage['product_name'] }}</td>
                                <td>{{ $usage['product_code'] }}</td>
                                <td>{{ number_format($usage['quantity'], 2, ',', '.') }}</td>
                                <td>{{ $usage['unit'] }}</td>
                                <td>{{ number_format($usage['material_cost'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-2 text-muted">No Material Usage recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- HPP Summary -->
    <div class="accordion-item shadow-sm border-0 mb-2">
        <h2 class="accordion-header" id="headingHppSummary">
            <button class="accordion-button py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHppSummary" aria-expanded="true" aria-controls="collapseHppSummary">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Material Cost & HPP Summary</h6>
            </button>
        </h2>
        <div id="collapseHppSummary" class="accordion-collapse collapse show" aria-labelledby="headingHppSummary">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 14px;">
                        <tbody>
                            @foreach($groupedUsage ?? [] as $category => $usages)
                            <tr>
                                <td class="fw-bold">{{ $category }}</td>
                                <td class="text-end">Rp {{ number_format(collect($usages)->sum('material_cost'), 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td class="fw-bold text-primary">Total Material Cost</td>
                                <td class="text-end fw-bold text-primary">Rp {{ number_format($grandTotalMaterial ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Service Cost</td>
                                <td class="text-end">Rp {{ number_format(collect($serviceUsage)->sum('total_subtotal'), 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td class="fw-bold text-uppercase fs-6">Total HPP</td>
                                <td class="text-end fw-bold fs-6 text-danger">Rp {{ number_format($financialSummary['total_hpp'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Usage Summary -->
    <div class="accordion-item shadow-sm border-0 mb-2">
        <h2 class="accordion-header" id="headingService">
            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseService" aria-expanded="false" aria-controls="collapseService">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;">Service Usage Summary</h6>
            </button>
        </h2>
        <div id="collapseService" class="accordion-collapse collapse" aria-labelledby="headingService">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 14px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Service Name</th>
                                <th>Total Quantity Used</th>
                                <th class="text-end">Total Revenue (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceUsage as $service)
                            <tr>
                                <td>{{ $service['service_name'] }}</td>
                                <td>{{ number_format($service['total_quantity'], 2, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($service['total_subtotal'], 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-2 text-muted">No Service Usage recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
