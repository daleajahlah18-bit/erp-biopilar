
@extends("layouts.app")
@section("title", "Sales Payment")
@section("page_title", "Sales Payment")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penerimaan Pembayaran</h5>
        <a href="{{ route("sales.payments.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Terima Pembayaran
        </a>
    </div>
    <div class="card-body">
        @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th>No Payment</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('invoice_ref', 'Invoice Ref')</th>
                        <th>@sortablelink('project_/_customer', 'Project / Customer')</th>
                        <th>Nominal</th>
                        <th>@sortablelink('metode', 'Metode')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                        <tr>
                            <td>{{ $pay->payment_number }}</td>
                            <td>{{ date("d/m/Y", strtotime($pay->payment_date)) }}</td>
                            <td>{{ $pay->salesInvoice->invoice_number }}</td>
                            <td>{{ $pay->salesInvoice->salesOrder->project->client_name ?? ($pay->salesInvoice->salesOrder->customer->customer_name ?? '-') }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($pay->payment_amount, 2, ",", ".") }}</td>
                            <td>{{ $pay->payment_method }}</td>
                            <td>
                                <a href="{{ route("sales.payments.show", $pay->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada data pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
