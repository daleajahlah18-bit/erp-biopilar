@extends('layouts.app')
@section('title', 'Transfer Stok')
@section('page_title', 'Transfer Stok')
@section('header_actions')
<a href="{{ route('inventory.transfers.create') }}" class="btn-primary-custom"><i class="bi bi-arrow-left-right"></i> Transfer Baru</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. Transfer</th><th>@sortablelink('gudang_asal', 'Gudang Asal')</th><th>@sortablelink('gudang_tujuan', 'Gudang Tujuan')</th><th>@sortablelink('created_at', 'Tanggal')</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($transfers as $transfer)
                    <tr>
                        <td><strong>{{ $transfer->transfer_number }}</strong></td>
                        <td>{{ $transfer->sourceWarehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $transfer->destinationWarehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $transfer->transfer_date }}</td>
                        <td><a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
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