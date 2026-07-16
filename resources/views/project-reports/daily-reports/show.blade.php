@extends('layouts.app')
@section('title', 'View Daily Report')
@section('page_title', 'View Daily Report')
@section('header_actions')
    <a href="{{ route('daily-reports.index') }}" class="btn btn-secondary">Kembali</a>
    <a href="{{ route('daily-reports.pdf', $dailyReport) }}" target="_blank" class="btn btn-danger-custom"><i class="bi bi-file-pdf"></i> Download PDF</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detail Laporan</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><td class="text-muted">No. Report</td><th>{{ $dailyReport->report_number }}</th></tr>
                    <tr><td class="text-muted">Project</td><th>{{ $dailyReport->project->project_name ?? '-' }}</th></tr>
                    <tr><td class="text-muted">Tanggal</td><th>{{ $dailyReport->report_date->format('d M Y') }}</th></tr>
                    <tr><td class="text-muted">Cuaca</td><th>{{ $dailyReport->weather }}</th></tr>
                    <tr><td class="text-muted">Bidang Pekerjaan</td><th>{{ $dailyReport->field_of_work ?? '-' }}</th></tr>
                    <tr><td class="text-muted">Paket Pekerjaan</td><th>{{ $dailyReport->work_package ?? '-' }}</th></tr>
                </table>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Man Power</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Posisi</th><th class="text-center">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach($dailyReport->manpower as $mp)
                        <tr><td>{{ $mp->position }}</td><td class="text-center">{{ $mp->quantity }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Pekerjaan & Evaluasi</h5></div>
            <div class="card-body">
                <h6>Pekerjaan yang dilaksanakan:</h6>
                <p class="border p-2 rounded bg-light">{{ $dailyReport->work_description ?? '-' }}</p>
                
                <h6 class="mt-3">Catatan dan Evaluasi:</h6>
                <p class="border p-2 rounded bg-light">{{ $dailyReport->evaluation_notes ?? '-' }}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Supply Material</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>Material</th><th class="text-center">Volume</th></tr></thead>
                            <tbody>
                                @foreach($dailyReport->materials as $mat)
                                <tr><td>{{ $mat->material_name }}</td><td class="text-center">{{ $mat->volume }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Alat Kerja</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>Alat</th><th class="text-center">Qty</th><th class="text-center">Unit</th></tr></thead>
                            <tbody>
                                @foreach($dailyReport->tools as $tool)
                                <tr><td>{{ $tool->tool_name }}</td><td class="text-center">{{ $tool->quantity }}</td><td class="text-center">{{ $tool->unit }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Dokumentasi</h5></div>
            <div class="card-body">
                <div class="row">
                    @foreach($dailyReport->documentations as $doc)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <a href="{{ asset('storage/' . $doc->photo) }}" target="_blank">
                                <img src="{{ asset('storage/' . $doc->photo) }}" class="card-img-top" style="height:150px; object-fit:cover;">
                            </a>
                            @if($doc->caption)
                            <div class="card-body p-2 text-center text-muted small">
                                {{ $doc->caption }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection
