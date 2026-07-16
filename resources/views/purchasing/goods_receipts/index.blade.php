@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('page_title', 'Goods Receipt')
@section('header_actions')
<a href="{{ route('purchasing.goods-receipts.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Terima Barang</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>No. GR</th><th>NO. PR</th><th>@sortablelink('gudang', 'Gudang')</th><th>@sortablelink('penerima', 'Penerima')</th><th>@sortablelink('created_at', 'Tanggal')</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr>
                        <td><strong>{{ $receipt->gr_number }}</strong></td>
                        <td>{{ $receipt->purchaseOrder->po_number ?? '-' }}</td>
                        <td>{{ $receipt->warehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $receipt->received_by }}</td>
                        <td>{{ $receipt->receipt_date }}</td>
                        <td><a href="{{ route('purchasing.goods-receipts.show', $receipt) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
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
