
@extends("layouts.app")
@section("title", "Sales Order")
@section("page_title", "Sales Order")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Sales Order</h5>
        <a href="{{ route("sales.orders.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Buat Sales Order
        </a>
    </div>
    <div class="card-body">
        @if(session("success"))
            <div class="alert alert-success">{{ session("success") }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th>No Order</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('project_/_customer', 'Project / Customer')</th>
                        <th>@sortablelink('total', 'Total')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->sales_order_number }}</td>
                            <td>{{ date("d/m/Y", strtotime($order->sales_order_date)) }}</td>
                            <td>{{ $order->project->project_name ?? ($order->customer->customer_name ?? '-') }}</td>
                            <td class="text-end">Rp {{ number_format($order->total_amount, 2, ",", ".") }}</td>
                            <td>
                                @if($order->status == "Draft") <span class="badge bg-secondary">Draft</span>
                                @elseif($order->status == "Confirmed") <span class="badge bg-primary">Confirmed</span>
                                @elseif($order->status == "Invoiced") <span class="badge bg-success">Invoiced</span>
                                @else <span class="badge bg-danger">Cancelled</span> @endif
                            </td>
                            <td>
                                <a href="{{ route("sales.orders.show", $order->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Belum ada pesanan penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
