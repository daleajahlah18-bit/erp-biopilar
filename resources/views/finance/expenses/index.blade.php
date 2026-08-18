@extends('layouts.app')

@section('page_title', 'Expenses')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Expenses</li>
        </ol>
    </nav>
@endsection

@section('header_actions')
    @can('finance.create')
        <a href="{{ route('finance.expenses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Record Expense
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="{{ route('finance.expenses.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Expense No, Desc...">
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@sortablelink('expense_number', 'Expense No')</th>
                        <th>@sortablelink('expense_date', 'Date')</th>
                        <th>Project</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">@sortablelink('amount', 'Amount')</th>
                        <th>@sortablelink('payment_method', 'Payment')</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td><strong>{{ $expense->expense_number }}</strong></td>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->project->project_name }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td>{{ Str::limit($expense->description, 40) }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($expense->amount, 2, ',', '.') }}</td>
                            <td>{{ $expense->payment_method }}</td>
                            <td class="text-end text-nowrap">
                                @can('finance.view')
                                    <a href="{{ route('finance.expenses.show', $expense) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('finance.edit')
                                    <a href="{{ route('finance.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('finance.print')
                                    <a href="{{ route('finance.expenses.pdf', $expense) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="PDF">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                @endcan
                                @can('finance.delete')
                                    <form action="{{ route('finance.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No expenses found matching the criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $expenses->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
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
