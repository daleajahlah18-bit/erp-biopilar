@extends('layouts.app')

@section('page_title', 'Finance Dashboard')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Finance Dashboard</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0 bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0 fw-normal">Total Expenses</h6>
                    <div class="bg-white bg-opacity-25 rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0 bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0 fw-normal">This Month</h6>
                    <div class="bg-white bg-opacity-25 rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-calendar-event fs-5"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bold">Rp {{ number_format($totalMonthExpense, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="card-title mb-0 fw-bold">Expenses by Category</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            @forelse($expenseByCategory as $cat)
                            <tr>
                                <td class="ps-0">{{ $cat->category_name }}</td>
                                <td class="text-end pe-0 fw-semibold">Rp {{ number_format($cat->total, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-muted text-center py-3">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="card-title mb-0 fw-bold">Top 5 Projects by Expense</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            @forelse($expenseByProject as $proj)
                            <tr>
                                <td class="ps-0">{{ Str::limit($proj->project_name, 35) }}</td>
                                <td class="text-end pe-0 fw-semibold text-primary">Rp {{ number_format($proj->total, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-muted text-center py-3">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom pt-4 pb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold">Latest Expenses</h6>
            <a href="{{ route('finance.expenses.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Expense No</th>
                        <th>Category</th>
                        <th>Project</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestExpenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('finance.expenses.show', $expense) }}" class="text-decoration-none fw-semibold">
                                {{ $expense->expense_number }}
                            </a>
                        </td>
                        <td>{{ $expense->category->name }}</td>
                        <td>{{ Str::limit($expense->project->project_name ?? '-', 30) }}</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No recent expenses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
