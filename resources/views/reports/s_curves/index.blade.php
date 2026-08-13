@extends('layouts.app')
@section('title', 'S-Curve')
@section('page_title', 'S-Curve Project Progress')
@section('header_actions')
<a href="{{ route('s-curves.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat S-Curve Baru</a>
@endsection

@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-funnel"></i> Filter & Search S-Curve</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('s-curves.index') }}" method="GET" id="filterForm">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Pilih Project</label>
                    <select name="project_id" class="form-select select2">
                        <option value="">Semua Project</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->project_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Dari Tanggal Mulai</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Sampai Tanggal Mulai</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary" onclick="return validateDateRange()"><i class="bi bi-search"></i> Filter</button>
                    <a href="{{ route('s-curves.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-custom table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Project Name</th>
                        <th>S-Curve Name</th>
                        <th>Duration</th>
                        <th>Created By</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sCurves as $sc)
                    <tr>
                        <td>{{ $loop->iteration + $sCurves->firstItem() - 1 }}</td>
                        <td>
                            <strong>{{ $sc->project->project_name ?? '-' }}</strong>
                            <div class="small text-muted">{{ $sc->project->client_name ?? '-' }}</div>
                        </td>
                        <td>{{ $sc->name }}</td>
                        <td>
                            <div>Start: {{ \Carbon\Carbon::parse($sc->start_date)->format('d M Y') }}</div>
                            <div class="small text-muted">End: {{ \Carbon\Carbon::parse($sc->end_date)->format('d M Y') }}</div>
                        </td>
                        <td>{{ $sc->creator->name ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('s-curves.show', $sc) }}" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i> Dashboard</a>
                            <form action="{{ route('s-curves.destroy', $sc) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); confirmDelete(() => this.closest('form').submit(), 'Hapus Data?', 'Yakin hapus S-Curve ini?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada data S-Curve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sCurves->hasPages()) 
        <div class="card-footer border-top">
            {{ $sCurves->links() }}
        </div> 
    @endif
</div>

<script>
function validateDateRange() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;

    if (dateFrom && dateTo) {
        if (new Date(dateFrom) > new Date(dateTo)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Filter tanggal tidak valid',
                    text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
                });
            } else {
                alert('Filter tanggal tidak valid: Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            }
            return false;
        }
    }
    return true;
}
</script>
@endsection
