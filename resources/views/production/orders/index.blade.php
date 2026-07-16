@extends('layouts.app')
@section('title', 'Production Order')
@section('page_title', 'Production Order')
@section('header_actions')
<a href="{{ route('production.orders.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat Order Produksi</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. Produksi</th><th>@sortablelink('produk_target', 'Produk Target')</th><th>@sortablelink('target_qty', 'Target Qty')</th><th>@sortablelink('status', 'Status')</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->production_number }}</strong></td>
                        <td>{{ $order->billOfMaterial?->product?->product_name ?? '-' }}</td>
                        <td>{{ rtrim(rtrim(number_format($order->target_quantity, 2, ',', '.'), '0'), ',') }}</td>
                        <td>
                            @if($order->status === 'Draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($order->status === 'In Progress')
                                <span class="badge bg-warning ">In Progress</span>
                            @elseif($order->status === 'Completed')
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-danger">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td><a href="{{ route('production.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection