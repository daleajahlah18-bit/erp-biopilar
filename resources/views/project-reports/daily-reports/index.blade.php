@extends('layouts.app')
@section('title', 'Daily Report')
@section('page_title', 'Daily Report')
@section('header_actions')
    @can('daily_report.create')
    <a href="{{ route('daily-reports.create') }}" class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i> Tambah Daily Report
    </a>
    @endcan
@endsection
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('daily-reports.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">Semua Project</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        <option value="">Semua</option>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$i,1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua</option>
                        @for($i=date('Y'); $i>=2020; $i--)
                            <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cuaca</label>
                    <select name="weather" class="form-select">
                        <option value="">Semua</option>
                        <option value="Cerah" {{ request('weather') == 'Cerah' ? 'selected' : '' }}>☀ Cerah</option>
                        <option value="Mendung" {{ request('weather') == 'Mendung' ? 'selected' : '' }}>☁ Mendung</option>
                        <option value="Hujan" {{ request('weather') == 'Hujan' ? 'selected' : '' }}>🌧 Hujan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1"><i class="bi bi-search"></i> Filter</button>
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-custom">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>No. Report</th>
                        <th>@sortablelink('created_at', 'Tanggal')</th>
                        <th>@sortablelink('project', 'Project')</th>
                        <th>@sortablelink('cuaca', 'Cuaca')</th>
                        <th>@sortablelink('dibuat_oleh', 'Dibuat Oleh')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td><strong>{{ $report->report_number }}</strong></td>
                        <td>{{ $report->report_date->format('d M Y') }}</td>
                        <td>{{ $report->project->project_name ?? '-' }}</td>
                        <td>
                            @if($report->weather == 'Cerah') <span class="badge badge-lunas">☀ Cerah</span>
                            @elseif($report->weather == 'Mendung') <span class="badge badge-sebagian">☁ Mendung</span>
                            @else <span class="badge badge-belum">🌧 Hujan</span> @endif
                        </td>
                        <td>{{ $report->creator->name ?? 'Unknown' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                @can('daily_report.view')
                                <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                                @endcan
                                
                                @can('daily_report.export_pdf')
                                <a href="{{ route('daily-reports.pdf', $report) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-pdf"></i> PDF</a>
                                @endcan
                                
                                @can('daily_report.edit')
                                <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                @endcan
                                
                                @can('daily_report.delete')
                                <form action="{{ route('daily-reports.destroy', $report) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(() => this.submit(), 'Hapus Data?', 'Hapus laporan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger-custom"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data daily report.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
    <div class="card-footer bg-white border-top">
        {{ $reports->links() }}
    </div>
    @endif
</div>
@endsection
