@extends('layouts.app')

@section('page_title', 'Finance Reports')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom pt-4 pb-3">
        <h6 class="card-title mb-0 fw-bold">Filter Report</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.reports.index') }}" method="GET" id="reportForm">
            <!-- Hidden input to track if we're filtering -->
            <input type="hidden" name="filter" value="1">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select select2">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100" onclick="document.getElementById('exportInput').disabled = true;">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                    @if(request()->has('filter'))
                        @can('finance.export')
                            <button type="submit" class="btn btn-danger w-100" onclick="document.getElementById('exportInput').disabled = false;" formtarget="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                        @endcan
                    @endif
                </div>
            </div>
            <!-- Hidden input for PDF export -->
            <input type="hidden" name="export" value="pdf" id="exportInput" disabled>
        </form>
    </div>
</div>

@if(request()->has('filter'))
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="card-title mb-0 fw-bold">Report Results</h6>
            <span class="badge bg-primary fs-6">Total: Rp {{ number_format($totalAmount, 2, ',', '.') }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Expense No</th>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Payment</th>
                            <th class="text-end">Amount (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="text-nowrap">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td>{{ $expense->expense_number }}</td>
                                <td>{{ $expense->project->project_name ?? '-' }}</td>
                                <td>{{ $expense->category->name ?? '-' }}</td>
                                <td>{{ Str::limit($expense->description, 40) }}</td>
                                <td>{{ $expense->payment_method }}</td>
                                <td class="text-end text-nowrap fw-semibold">{{ number_format($expense->amount, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No expenses found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($expenses->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">GRAND TOTAL</td>
                            <td class="text-end text-primary fs-6">Rp {{ number_format($totalAmount, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info border-0 shadow-sm text-center py-5">
        <i class="bi bi-funnel fs-1 text-info mb-3 d-block"></i>
        <h5>Please apply filters to view the report</h5>
        <p class="text-muted mb-0">Select your criteria above and click 'Apply Filter' to generate the data.</p>
    </div>
@endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'default'
    });
});
</script>
@endsection
