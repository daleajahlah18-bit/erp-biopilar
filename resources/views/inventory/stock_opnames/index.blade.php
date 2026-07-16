@extends('layouts.app')
@section('title', 'Data Stock Opname')
@section('page_title', 'Stock Opname')
@section('page_subtitle', 'Riwayat penyesuaian stok fisik')

@section('header_actions')
<a href="{{ route('inventory.stock-opnames.create') }}" class="btn-primary-custom text-decoration-none">
    <i class="bi bi-plus-lg"></i> Buat Opname Baru
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>No Opname</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('gudang', 'Gudang')</th>
                        <th>@sortablelink('catatan', 'Catatan')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $opname)
                    <tr>
                        <td class="fw-bold">{{ $opname->opname_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($opname->opname_date)->format('d/m/Y') }}</td>
                        <td>{{ $opname->warehouse->warehouse_name ?? '-' }}</td>
                        <td>{{ $opname->notes ?? '-' }}</td>
                        <td>
                            <a href="{{ route('inventory.stock-opnames.show', $opname->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Belum ada riwayat stock opname.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opnames->hasPages())
        <div class="p-3 border-top">
            {{ $opnames->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
