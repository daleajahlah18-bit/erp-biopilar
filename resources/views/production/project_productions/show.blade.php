@extends('layouts.app')
@section('title', 'Detail Project Fabrication')
@section('page_title', 'Project Fabrication')
@section('page_subtitle', 'Detail Dokumen')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header  py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Dokumen</h6>
                <a href="{{ route('production.project-productions.pdf', $projectProduction->id) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td width="40%" class="text-muted">No Dokumen</td>
                        <td>: <strong>{{ $projectProduction->project_production_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>: {{ \Carbon\Carbon::parse($projectProduction->production_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>: 
                            @if($projectProduction->status === 'Finalized')
                                <span class="badge bg-success">Finalized</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Project</td>
                        <td>: {{ $projectProduction->project->project_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Gudang Asal</td>
                        <td>: {{ $projectProduction->warehouse->warehouse_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Oleh</td>
                        <td>: {{ $projectProduction->creator->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Pada</td>
                        <td>: {{ $projectProduction->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Catatan</td>
                        <td>: {{ $projectProduction->notes ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header  py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Pemakaian Barang</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="">
                            <tr>
                                <th>No</th>
                                <th>Produk / Barang</th>
                                <th>Qty Dipakai</th>
                                <th>Unit</th>
                                <th>HPP Satuan (Rp)</th>
                                <th>Material Cost (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projectProduction->details as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($detail->billOfMaterial)
                                        <small class="badge bg-info text-white mb-1">BOM: {{ $detail->billOfMaterial->bom_name }}</small><br>
                                    @endif
                                    {{ $detail->product->product_name ?? '-' }}<br>
                                    <small class="text-muted">{{ $detail->product->product_code ?? '-' }}</small>
                                </td>
                                <td class="fw-bold text-primary">{{ number_format($detail->quantity, 2, ',', '.') }}</td>
                                <td>{{ $detail->unit->unit_name ?? '-' }}</td>
                                <td>{{ number_format($detail->bom_hpp, 0, ',', '.') }}</td>
                                <td>{{ number_format($detail->material_cost, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end">
            <a href="{{ route('production.project-productions.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
