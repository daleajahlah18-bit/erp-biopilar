@extends('layouts.app')
@section('title', 'Detail Bill of Material')
@section('page_title', 'Bill of Material')
@section('page_subtitle', 'Detail Resep')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header  py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi BOM</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td width="40%" class="text-muted">No BOM</td>
                        <td>: <strong>{{ $bom->bom_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama BOM</td>
                        <td>: {{ $bom->bom_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Produk Jadi</td>
                        <td>: {{ $bom->product ? $bom->product->product_name : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Oleh</td>
                        <td>: {{ $bom->creator->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Pada</td>
                        <td>: {{ $bom->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Catatan</td>
                        <td>: {{ $bom->notes ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header  py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Komposisi / Bahan Baku</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="">
                            <tr>
                                <th>No</th>
                                <th>Produk / Bahan</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th class="text-end">Harga (Rp)</th>
                                <th class="text-end">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bom->details as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $detail->product->product_name }}<br>
                                    <small class="text-muted">{{ $detail->product->product_code }}</small>
                                </td>
                                <td>{{ number_format($detail->quantity, 2, ',', '.') }}</td>
                                <td>{{ $detail->unit->unit_name }}</td>
                                <td class="text-end">{{ number_format($detail->unit_cost, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($detail->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">TOTAL HPP / COGS :</td>
                                <td class="text-end fw-bold text-primary" style="font-size: 1.1rem;">
                                    Rp{{ number_format($bom->total_hpp, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('production.bom.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('production.bom.edit', $bom->id) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit BOM</a>
        </div>
    </div>
</div>
@endsection