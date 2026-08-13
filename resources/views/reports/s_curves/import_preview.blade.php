@extends('layouts.app')
@section('title', 'S-Curve Import Preview')
@section('page_title', 'Preview & Validation')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Data Parsing Results</h6>
                <span class="badge {{ $parsedData['validation']['is_valid'] ? 'bg-success' : 'bg-danger' }}">
                    {{ $parsedData['validation']['is_valid'] ? 'Ready to Import' : 'Validation Error' }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold mb-3">Project & Import Details</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted w-25">Project</td><td class="fw-bold">{{ $project->project_number }} - {{ $project->project_name }}</td></tr>
                            <tr><td class="text-muted">S-Curve Name</td><td class="fw-bold">{{ $parsedData['name'] }}</td></tr>
                            <tr><td class="text-muted">Period</td><td class="fw-bold">{{ \Carbon\Carbon::parse($parsedData['start_date'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($parsedData['end_date'])->format('d M Y') }}</td></tr>
                            <tr><td class="text-muted">Source Sheet</td><td class="fw-bold text-primary">{{ $parsedData['sheet_name'] }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Structure Detection</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted w-25">Total Weeks</td><td class="fw-bold">{{ $parsedData['weeks'] }} Weeks <span class="text-success small ms-2"><i class="bi bi-check-circle-fill"></i></span></td></tr>
                            <tr><td class="text-muted">WBS Activities</td><td class="fw-bold">{{ count($parsedData['flat_items']) }} Items <span class="text-success small ms-2"><i class="bi bi-check-circle-fill"></i></span></td></tr>
                            <tr>
                                <td class="text-muted">Total Leaf Weight</td>
                                <td class="fw-bold {{ abs($parsedData['validation']['total_leaf_weight'] - 100) > 0.1 ? 'text-danger' : 'text-success' }}">
                                    {{ round($parsedData['validation']['total_leaf_weight'], 2) }}% 
                                    @if(abs($parsedData['validation']['total_leaf_weight'] - 100) <= 0.1)
                                        <span class="small ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                    @else
                                        <span class="small ms-2"><i class="bi bi-x-circle-fill"></i></span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if(!empty($parsedData['validation']['errors']) || !empty($parsedData['validation']['warnings']))
                    <div class="mb-4">
                        @if(!empty($parsedData['validation']['errors']))
                            <div class="alert alert-danger mb-2">
                                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Critical Errors Detected:</h6>
                                <ul class="mb-0">
                                    @foreach($parsedData['validation']['errors'] as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($parsedData['validation']['warnings']))
                            <div class="alert alert-warning mb-0">
                                <h6 class="fw-bold"><i class="bi bi-exclamation-circle-fill"></i> Warnings:</h6>
                                <ul class="mb-0">
                                    @foreach($parsedData['validation']['warnings'] as $warn)
                                        <li>{{ $warn }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                <h6 class="fw-bold mt-4 mb-3">WBS Preview (Plan vs Actual Detection)</h6>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered table-sm" style="font-size: 0.85rem;">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th>Code</th>
                                <th>Work Name</th>
                                <th class="text-center">Weight</th>
                                <th class="text-center">Plan Data</th>
                                <th class="text-center">Actual Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parsedData['items'] as $item)
                                @if($item['is_parent'])
                                    <tr class="bg-light fw-bold">
                                        <td>{{ $item['code'] }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-center">{{ round($item['weight'], 2) }}%</td>
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted">-</td>
                                    </tr>
                                    @foreach($item['children'] as $child)
                                        <tr>
                                            <td class="ps-3">{{ $child['code'] }}</td>
                                            <td class="ps-3">{{ $child['name'] }}</td>
                                            <td class="text-center">{{ round($child['weight'], 2) }}%</td>
                                            <td class="text-center {!! count($child['plan']) > 0 ? 'text-success' : 'text-danger' !!}">
                                                {!! count($child['plan']) > 0 ? '<i class="bi bi-check-circle"></i> Detected' : '<i class="bi bi-x-circle"></i> None' !!}
                                            </td>
                                            <td class="text-center {!! count($child['actual']) > 0 ? 'text-success' : 'text-muted' !!}">
                                                {!! count($child['actual']) > 0 ? '<i class="bi bi-check-circle"></i> Detected' : '-' !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ $item['code'] }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-center">{{ round($item['weight'], 2) }}%</td>
                                        <td class="text-center {!! count($item['plan']) > 0 ? 'text-success' : 'text-danger' !!}">
                                            {!! count($item['plan']) > 0 ? '<i class="bi bi-check-circle"></i> Detected' : '<i class="bi bi-x-circle"></i> None' !!}
                                        </td>
                                        <td class="text-center {!! count($item['actual']) > 0 ? 'text-success' : 'text-muted' !!}">
                                            {!! count($item['actual']) > 0 ? '<i class="bi bi-check-circle"></i> Detected' : '-' !!}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-white text-end">
                <form action="{{ route('s-curves.import.confirm') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <a href="{{ route('s-curves.import') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary" {{ !$parsedData['validation']['is_valid'] ? 'disabled' : '' }} onclick="this.innerHTML='<i class=\'spinner-border spinner-border-sm\'></i> Importing...'; this.form.submit();">
                        <i class="bi bi-cloud-arrow-up"></i> Confirm & Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
