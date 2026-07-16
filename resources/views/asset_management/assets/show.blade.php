@extends('layouts.app')
@section('title', 'Asset Detail')
@section('page_title', 'Asset Detail: ' . $asset->asset_name)

@section('content')
<div class="row">
    <div class="col-12">
        
        <!-- Two Depreciation Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">Commercial Book</div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th>Method</th><td>{{ $asset->commercial_method }}</td></tr>
                            <tr><th>Useful Life</th><td>{{ $asset->commercial_useful_life }} Months</td></tr>
                            <tr><th>Remaining Life</th><td>{{ $asset->commercial_remaining_life }} Months</td></tr>
                            <tr><th>Monthly Depr.</th><td>Rp {{ number_format($asset->commercial_monthly_depreciation, 0, ',', '.') }}</td></tr>
                            <tr><th>Accumulated</th><td>Rp {{ number_format($asset->commercial_accumulated_depreciation, 0, ',', '.') }}</td></tr>
                            <tr><th>Net Book Value</th><td class="fs-5 fw-bold text-primary">Rp {{ number_format($asset->commercial_book_value, 0, ',', '.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success text-white">Fiscal Book</div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th>Method</th><td>{{ $asset->fiscal_method }}</td></tr>
                            <tr><th>Useful Life</th><td>{{ $asset->fiscal_useful_life }} Months</td></tr>
                            <tr><th>Remaining Life</th><td>{{ $asset->fiscal_remaining_life }} Months</td></tr>
                            <tr><th>Monthly Depr.</th><td>Rp {{ number_format($asset->fiscal_monthly_depreciation, 0, ',', '.') }}</td></tr>
                            <tr><th>Accumulated</th><td>Rp {{ number_format($asset->fiscal_accumulated_depreciation, 0, ',', '.') }}</td></tr>
                            <tr><th>Net Book Value</th><td class="fs-5 fw-bold text-success">Rp {{ number_format($asset->fiscal_book_value, 0, ',', '.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="assetTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="commercial-tab" data-bs-toggle="tab" data-bs-target="#commercial" type="button" role="tab">Commercial Schedule (Dynamic)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="fiscal-tab" data-bs-toggle="tab" data-bs-target="#fiscal" type="button" role="tab">Fiscal Schedule (Dynamic)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">Maintenance</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="capex-tab" data-bs-toggle="tab" data-bs-target="#capex" type="button" role="tab">Capital Improvement</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="movement-tab" data-bs-toggle="tab" data-bs-target="#movement" type="button" role="tab">Movement History</button>
            </li>
        </ul>

        <div class="tab-content" id="assetTabsContent">
            <!-- Overview -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>{{ $asset->asset_code }} - {{ $asset->asset_name }}</h5>
                        <p><strong>Status:</strong> {{ $asset->status }}</p>
                        <p><strong>Location:</strong> {{ $asset->location }}</p>
                        <p><strong>Department:</strong> {{ $asset->department }}</p>
                        <p><strong>PIC:</strong> {{ $asset->responsible_person }}</p>
                        <p><strong>Acquisition Cost:</strong> Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Commercial Depreciation -->
            <div class="tab-pane fade" id="commercial" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Commercial Depreciation Schedule (Dynamically Generated)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Period</th>
                                        <th>Beginning BV</th>
                                        <th>CapEx</th>
                                        <th>Expense</th>
                                        <th>Accumulated</th>
                                        <th>Ending BV</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($commercialDepreciations as $depr)
                                    <tr>
                                        <td>{{ $depr['period'] }}</td>
                                        <td>Rp {{ number_format($depr['beginning_book_value'], 0, ',', '.') }}</td>
                                        <td>{{ $depr['capex'] > 0 ? 'Rp '.number_format($depr['capex'], 0, ',', '.') : '-' }}</td>
                                        <td>Rp {{ number_format($depr['expense'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($depr['accumulated_depreciation'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($depr['ending_book_value'], 0, ',', '.') }}</td>
                                        <td>{{ $depr['method_used'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fiscal Depreciation -->
            <div class="tab-pane fade" id="fiscal" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Fiscal Depreciation Schedule (Dynamically Generated)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Period</th>
                                        <th>Beginning BV</th>
                                        <th>CapEx</th>
                                        <th>Expense</th>
                                        <th>Accumulated</th>
                                        <th>Ending BV</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fiscalDepreciations as $depr)
                                    <tr>
                                        <td>{{ $depr['period'] }}</td>
                                        <td>Rp {{ number_format($depr['beginning_book_value'], 0, ',', '.') }}</td>
                                        <td>{{ $depr['capex'] > 0 ? 'Rp '.number_format($depr['capex'], 0, ',', '.') : '-' }}</td>
                                        <td>Rp {{ number_format($depr['expense'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($depr['accumulated_depreciation'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($depr['ending_book_value'], 0, ',', '.') }}</td>
                                        <td>{{ $depr['method_used'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="tab-pane fade" id="maintenance" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Maintenance History</h5>
                        <ul>
                            @foreach($asset->maintenances as $maint)
                            <li>{{ $maint->maintenance_date }} - {{ $maint->maintenance_type }} - Cost: Rp {{ number_format($maint->cost, 0, ',', '.') }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Capital Improvement -->
            <div class="tab-pane fade" id="capex" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Capital Improvement History</h5>
                        <ul>
                            @foreach($asset->improvements as $capex)
                            <li>{{ $capex->improvement_date }} - Cost: Rp {{ number_format($capex->improvement_cost, 0, ',', '.') }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Movement -->
            <div class="tab-pane fade" id="movement" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Movement History</h5>
                        <ul>
                            @foreach($asset->movements as $mov)
                            <li>{{ $mov->movement_date }} - From {{ $mov->from_location }} to {{ $mov->to_location }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
