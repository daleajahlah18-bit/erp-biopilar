@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h3 text-gray-800">Dashboard Purchase Payable (Hutang Supplier)</h2>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Hutang Berjalan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalHutang, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Hutang Jatuh Tempo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($hutangJatuhTempo, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Belum Jatuh Tempo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($hutangBelumJatuhTempo, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Supplier Menunggak</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $jumlahSupplierNunggak }} Supplier</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('purchasing.payables.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Supplier</label>
                        <select name="supplier_id" class="form-control">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="Unpaid" {{ request('status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Mulai Tanggal GR</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Sampai Tanggal GR</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="">
                    <tr>
                        <th>@sortablelink('gr_number', 'GR Number')</th>
                            <th>@sortablelink('pr_number', 'PR Number')</th>
                            <th>@sortablelink('supplier', 'Supplier')</th>
                            <th>@sortablelink('tgl_gr', 'Tgl GR')</th>
                            <th>@sortablelink('created_at', 'Due Date')</th>
                            <th>@sortablelink('total', 'Total Pembelian')</th>
                            <th>@sortablelink('total', 'Total Dibayar')</th>
                            <th>@sortablelink('sisa_hutang', 'Sisa Hutang')</th>
                            <th>@sortablelink('status', 'Status')</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payables as $gr)
                        @php
                            $isOverdue = ($gr->remaining_amount > 0 && $gr->due_date && $gr->due_date < now()->toDateString());
                        @endphp
                        <tr>
                            <td>{{ $gr->gr_number }}</td>
                            <td>{{ $gr->purchaseOrder->po_number ?? '-' }}</td>
                            <td>{{ $gr->purchaseOrder->supplier->supplier_name ?? '-' }}</td>
                            <td>{{ $gr->receipt_date }}</td>
                            <td>{{ $gr->due_date ?? '-' }}</td>
                            <td>Rp {{ number_format($gr->total_amount, 0, ',', '.') }}</td>
                            <td class="text-success">Rp {{ number_format($gr->total_paid, 0, ',', '.') }}</td>
                            <td class="text-danger font-weight-bold">Rp {{ number_format($gr->remaining_amount, 0, ',', '.') }}</td>
                            <td>
                                  @if($isOverdue)
                                      <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Overdue</span>
                                  @elseif($gr->payment_status == 'Paid')
                                      <span class="badge bg-success bg-opacity-10 text-success border border-success">Paid</span>
                                  @elseif($gr->payment_status == 'Partially Paid')
                                      <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Partially Paid</span>
                                  @else
                                      <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Unpaid</span>
                                  @endif
                            </td>
                            <td>
                                <a href="{{ route('purchasing.payables.show', $gr->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada data hutang.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $payables->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
