@extends("layouts.app")
@section("title", "Sales Receivable")
@section("page_title", "Monitoring Piutang Termin")

@section("content")

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('sales.receivables.index') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label>Filter Project</label>
                    <select name="project_id" class="form-select" onchange="this.form.submit()">
                        <option value="">[ Semua Project ]</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12 col-md-6 col-xl-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Total Project Value</h6>
                <h4 class="mb-0 fw-bold">Rp {{ number_format($summary["total_project_value"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Total Termin Value</h6>
                <h4 class="mb-0 fw-bold">Rp {{ number_format($summary["total_termin_value"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Total Paid</h6>
                <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($summary["total_paid"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Total Outstanding</h6>
                <h4 class="mb-0 fw-bold text-danger">Rp {{ number_format($summary["total_outstanding"], 0, ",", ".") }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Termin Paid</h6>
                <h4 class="mb-0 fw-bold text-primary">{{ $summary["termin_paid"] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-secondary">Termin Unpaid</h6>
                <h4 class="mb-0 fw-bold text-warning">{{ $summary["termin_unpaid"] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Piutang (Per Termin Project)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="">
                    <tr>
                        <th>@sortablelink('project', 'Project')</th>
                        <th>@sortablelink('invoice', 'Invoice')</th>
                        <th>@sortablelink('top', 'TOP')</th>
                        <th>@sortablelink('created_at', 'Due Date')</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">@sortablelink('sudah_dibayar', 'Sudah Dibayar')</th>
                        <th class="text-end">@sortablelink('sisa_tagihan', 'Sisa Tagihan')</th>
                        <th>@sortablelink('status', 'Status')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedTerms as $term)
                        @php
                            $isOverdue = $term->remaining_amount > 0 && now()->startOfDay()->greaterThan($term->due_date);
                        @endphp
                        <tr>
                            <td>{{ $term->project_name }}</td>
                            <td>{{ $term->invoice_number }}</td>
                            <td>{{ $term->top_type }} ({{ number_format($term->percentage, 2) }}%)</td>
                            <td>
                                {{ $term->due_date->format("d/m/Y") }}
                                @if($isOverdue) <span class="badge bg-danger rounded-pill ms-1">Overdue</span> @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($term->nominal, 2, ",", ".") }}</td>
                            <td class="text-end text-success">Rp {{ number_format($term->total_paid, 2, ",", ".") }}</td>
                            <td class="text-end text-danger fw-bold">Rp {{ number_format($term->remaining_amount, 2, ",", ".") }}</td>
                            <td>
                                @if($term->status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                                @elseif($term->status == "Partially Paid") <span class="badge bg-warning ">Partial</span>
                                @else <span class="badge bg-success">Paid</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Belum ada data piutang termin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $paginatedTerms->links() }}</div>
    </div>
</div>
@endsection
