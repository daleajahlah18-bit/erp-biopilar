@extends('layouts.app')
@section('title', 'Data Item Journal')
@section('page_title', 'Item Journal')
@section('page_subtitle', 'Riwayat pergerakan stok manual')

@section('header_actions')
<a href="{{ route('inventory.item-journals.create') }}" class="btn-primary-custom text-decoration-none">
    <i class="bi bi-plus-lg"></i> Jurnal Manual
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>No Jurnal</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('tipe', 'Tipe')</th>
                        <th>@sortablelink('gudang', 'Gudang')</th>
                        <th>@sortablelink('produk', 'Produk')</th>
                        <th class="text-end">@sortablelink('qty', 'Qty')</th>
                        <th>@sortablelink('keterangan', 'Keterangan')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $j)
                    <tr>
                        <td class="fw-bold">{{ $j->journal_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($j->transaction_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($j->transaction_type == 'Stock In')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">{{ $j->transaction_type }}</span>
                            @elseif($j->transaction_type == 'Stock Out')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">{{ $j->transaction_type }}</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $j->transaction_type }}</span>
                            @endif
                        </td>
                        <td>{{ $j->warehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $j->product->product_name ?? '-' }}</td>
                        <td class="text-end fw-bold">{{ number_format($j->quantity, 2, ',', '.') }}</td>
                        <td>{{ $j->description }}</td>
                        <td>
                            <a href="{{ route('inventory.item-journals.show', $j->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">Belum ada riwayat jurnal manual.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($journals->hasPages())
        <div class="p-3 border-top">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
