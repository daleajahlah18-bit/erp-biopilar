@extends('layouts.app')
@section('title', 'Purchase Release')
@section('page_title', 'Purchase Release')
@section('header_actions')
<a href="{{ route('purchasing.purchase-orders.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat PR</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>NO. PR</th><th>@sortablelink('supplier', 'Supplier')</th><th>@sortablelink('created_at', 'Tanggal')</th><th>@sortablelink('total', 'Total')</th><th>@sortablelink('status', 'Status')</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->po_number }}</strong></td>
                        <td>{{ $order->supplier->supplier_name ?? '-' }}</td>
                        <td>{{ $order->po_date }}</td>
                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td><span class="badge-status {{ $order->status == 'Draft' ? 'badge-draft' : 'badge-approved' }}">{{ $order->status }}</span></td>
                        <td>
                            <a href="{{ route('purchasing.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            @if(!$order->goodsReceipts()->exists())
                                <a href="{{ route('purchasing.purchase-orders.edit', $order) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
