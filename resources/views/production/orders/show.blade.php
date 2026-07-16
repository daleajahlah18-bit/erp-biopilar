@extends('layouts.app')
@section('title', 'Detail Production Order')
@section('page_title', 'Detail Production Order')
@section('page_subtitle', 'Informasi Lengkap Perintah Produksi')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary">Informasi Production Order</h5>
        <div>
            @if($order->status === 'Draft')
                <span class="badge bg-secondary p-2 px-3">Draft</span>
            @elseif($order->status === 'In Progress')
                <span class="badge bg-warning  p-2 px-3">In Progress</span>
            @elseif($order->status === 'Completed')
                <span class="badge bg-success p-2 px-3">Completed</span>
            @else
                <span class="badge bg-danger p-2 px-3">{{ $order->status }}</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr><td width="35%">No. Produksi</td><td width="5%">:</td><td><strong>{{ $order->production_number }}</strong></td></tr>
                    <tr><td>Tanggal</td><td>:</td><td>{{ \Carbon\Carbon::parse($order->production_date)->format('d F Y') }}</td></tr>
                    <tr><td>Dibuat Oleh</td><td>:</td><td>{{ $order->creator->name ?? 'Unknown' }}</td></tr>
                    <tr><td>Gudang</td><td>:</td><td>{{ $order->warehouse->warehouse_name ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr><td width="35%">Bill of Material</td><td width="5%">:</td><td>{{ $order->billOfMaterial?->bom_number ?? '-' }}</td></tr>
                    <tr><td>Produk Jadi</td><td>:</td><td><strong>{{ $order->billOfMaterial?->product?->product_name ?? 'Tanpa Produk Jadi' }}</strong></td></tr>
                    <tr><td>Target Quantity</td><td>:</td><td><span class="badge bg-primary px-2">{{ rtrim(rtrim(number_format($order->target_quantity, 2, ',', '.'), '0'), ',') }}</span></td></tr>
                    @if($order->status === 'Completed')
                    <tr><td>Actual Quantity</td><td>:</td><td><span class="badge bg-success px-2">{{ rtrim(rtrim(number_format($order->actual_quantity, 2, ',', '.'), '0'), ',') }}</span></td></tr>
                    <tr><td>Waste / Susut</td><td>:</td><td>
                        @php $waste = $order->target_quantity - $order->actual_quantity; @endphp
                        <span class="badge {{ $waste > 0 ? 'bg-danger' : 'bg-success' }} px-2">{{ rtrim(rtrim(number_format($waste, 2, ',', '.'), '0'), ',') }}</span>
                    </td></tr>
                    @endif
                </table>
            </div>
            @if($order->notes || $order->production_result_notes)
            <div class="col-12 mt-3">
                @if($order->notes)
                    <p class="mb-1"><strong>Catatan Produksi:</strong><br>{{ $order->notes }}</p>
                @endif
                @if($order->production_result_notes)
                    <p class="mb-0 text-success"><strong>Catatan Hasil Produksi (Result):</strong><br>{{ $order->production_result_notes }}</p>
                @endif
            </div>
            @endif
        </div>

        <h6 class="text-primary fw-bold mb-3">Rincian Kebutuhan Bahan Baku</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="35%">Product / Bahan</th>
                        <th width="15%">Unit</th>
                        <th width="15%" class="text-end">Qty per BOM</th>
                        <th width="15%" class="text-end">Stok Tersedia Saat Ini</th>
                        <th width="15%" class="text-end">Qty Terpotong</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $detail->product->product_code ?? '-' }} - {{ $detail->product->product_name ?? 'Unknown' }}</td>
                        <td>{{ $detail->product->unit->unit_name ?? '-' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($detail->quantity_per_bom, 4, ',', '.'), '0'), ',') }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($detail->stock_available, 4, ',', '.'), '0'), ',') }}</td>
                        <td class="text-end fw-bold text-danger">{{ rtrim(rtrim(number_format($detail->quantity_required, 4, ',', '.'), '0'), ',') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('production.orders.index') }}" class="btn-outline-custom text-decoration-none">Kembali</a>
            
            @if($order->status === 'Draft')
                <form action="{{ route('production.orders.start-production', $order->id) }}" method="POST" class="d-inline" onsubmit="event.preventDefault(); confirmAction(() => this.submit(), 'Konfirmasi', 'Apakah Anda yakin ingin memulai produksi? Stok bahan baku akan langsung dipotong dari gudang!')">
                    @csrf
                    <button type="submit" class="btn-primary-custom"><i class="bi bi-play-fill"></i> Start Production</button>
                </form>
            @endif

            @if($order->status === 'In Progress')
                <a href="{{ route('production.orders.production-result', $order->id) }}" class="btn-success-custom text-decoration-none"><i class="bi bi-check2-circle"></i> Input Production Result</a>
            @endif
        </div>
    </div>
</div>
@endsection