
@extends("layouts.app")
@section("title", "Sales Invoice")
@section("page_title", "Sales Invoice")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Sales Invoice</h5>
        <a href="{{ route("sales.invoices.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Buat Invoice
        </a>
    </div>
    <div class="card-body">
        @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th>No Invoice</th>
                        <th>@sortablelink('so_ref', 'SO Ref')</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('project_/_customer', 'Project / Customer')</th>
                        <th>@sortablelink('total', 'Total')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>@sortablelink('pembayaran', 'Pembayaran')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->salesOrder->sales_order_number }}</td>
                            <td>{{ date("d/m/Y", strtotime($inv->invoice_date)) }}</td>
                            <td>{{ $inv->salesOrder->project->project_name ?? ($inv->salesOrder->customer->customer_name ?? '-') }}</td>
                            <td class="text-end">Rp {{ number_format($inv->total_amount, 2, ",", ".") }}</td>
                            <td>
                                @if($inv->status == "Draft") <span class="badge bg-secondary">Draft</span>
                                @elseif($inv->status == "Approved") <span class="badge bg-primary">Approved</span>
                                @else <span class="badge bg-success">{{ $inv->status }}</span> @endif
                            </td>
                            <td>
                                @if($inv->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                                @elseif($inv->payment_status == "Partially Paid") <span class="badge bg-warning ">Partial</span>
                                @else <span class="badge bg-success">Paid</span> @endif
                            </td>
                            <td>
                                <a href="{{ route("sales.invoices.show", $inv->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Belum ada invoice penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
