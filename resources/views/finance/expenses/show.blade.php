@extends('layouts.app')

@section('page_title', 'Expense Details')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
            <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $expense->expense_number }}</li>
        </ol>
    </nav>
@endsection

@section('header_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('finance.expenses.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        @can('finance.print')
            <a href="{{ route('finance.expenses.pdf', $expense) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer"></i> Print
            </a>
        @endcan
        @can('finance.edit')
            <a href="{{ route('finance.expenses.edit', $expense) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">Expense Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Expense Number</div>
                    <div class="col-sm-8 fw-semibold">{{ $expense->expense_number }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Expense Date</div>
                    <div class="col-sm-8">{{ $expense->expense_date->format('d M Y') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Project</div>
                    <div class="col-sm-8">{{ $expense->project->project_name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Category</div>
                    <div class="col-sm-8">{{ $expense->category->name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Description</div>
                    <div class="col-sm-8">{{ $expense->description }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Amount</div>
                    <div class="col-sm-8 fw-bold text-primary fs-5">Rp {{ number_format($expense->amount, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Payment Method</div>
                    <div class="col-sm-8">{{ $expense->payment_method }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Paid To</div>
                    <div class="col-sm-8">{{ $expense->paid_to ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Reference Number</div>
                    <div class="col-sm-8">{{ $expense->reference_number ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Notes</div>
                    <div class="col-sm-8">{{ $expense->notes ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">System Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Created By</div>
                    <div>{{ $expense->creator->name ?? 'System' }}</div>
                    <div class="text-muted small">{{ $expense->created_at->format('d M Y H:i') }}</div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Last Updated</div>
                    <div>{{ $expense->updater->name ?? ($expense->updated_at != $expense->created_at ? 'System' : '-') }}</div>
                    <div class="text-muted small">{{ $expense->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attachments</h5>
                <span class="badge bg-secondary">{{ $expense->attachments->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($expense->attachments->count() > 0)
                    <div class="list-group list-group-flush border-0">
                        @foreach($expense->attachments as $attachment)
                            <a href="{{ route('finance.expenses.download-attachment', [$expense, $attachment]) }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 border-0 border-bottom">
                                <div class="d-flex align-items-center gap-2 text-truncate pe-2">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    <span class="text-truncate" style="max-width: 150px;">{{ $attachment->file_name }}</span>
                                </div>
                                <div class="text-muted small text-nowrap">
                                    {{ number_format($attachment->file_size / 1024, 2) }} KB
                                    <i class="bi bi-download ms-1"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2 text-light"></i>
                        No attachments uploaded
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
