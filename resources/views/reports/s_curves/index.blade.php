@extends('layouts.app')
@section('title', 'S-Curve')
@section('page_title', 'S-Curve Project Progress')
@section('header_actions')
<div class="d-flex gap-2">
    <a href="{{ route('s-curves.import') }}" class="btn btn-success text-white"><i class="bi bi-file-earmark-excel"></i> Import Excel</a>
    <a href="{{ route('s-curves.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg"></i> Buat S-Curve Baru</a>
</div>
@endsection

@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<!-- Project Selection Card -->
<div class="card shadow-sm mb-4 border-left-primary">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-search"></i> Project Selection</h6>
    </div>
    <div class="card-body">
        <div class="position-relative">
            <label class="form-label text-muted small fw-bold">Search Project</label>
            <div style="position: relative;">
                <i class="bi bi-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                <input type="text" id="projectSearchInput" class="form-control" style="padding-left: 40px;" placeholder="Search project name / project number / client name..." autocomplete="off">
            </div>
            <!-- Search Results Dropdown -->
            <div id="projectSearchResults" class="dropdown-menu w-100 shadow-sm" style="max-height: 300px; overflow-y: auto; display: none; position: absolute; z-index: 1000; top: 100%;">
                <!-- AJAX results will be populated here -->
            </div>
        </div>

        <!-- Project Information Block -->
        <div id="projectInfoBlock" class="mt-4" style="display: none;">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="font-weight-bold mb-3 text-primary border-bottom pb-2">PROJECT INFORMATION</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Project Number</small>
                            <span id="infoProjectNumber" class="fw-bold">-</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Project Name</small>
                            <span id="infoProjectName" class="fw-bold">-</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Client</small>
                            <span id="infoClientName" class="fw-bold">-</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Project Period</small>
                            <span id="infoProjectPeriod" class="fw-bold">-</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded h-100 text-center d-flex flex-column justify-content-center" id="existingSCurveContainer">
                        <!-- S-Curve info or Create Button populated via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout = null;
    const searchInput = document.getElementById('projectSearchInput');
    const searchResults = document.getElementById('projectSearchResults');
    const infoBlock = document.getElementById('projectInfoBlock');

    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                infoBlock.style.display = 'none';
                return;
            }
            
            searchResults.innerHTML = '<div class="px-3 py-2 text-muted small"><i class="spinner-border spinner-border-sm me-2"></i> Searching...</div>';
            searchResults.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                fetch(`{{ route('s-curves.projects.search') }}?with_scurves=1&q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length === 0) {
                            searchResults.innerHTML = '<div class="px-3 py-2 text-muted">No projects found.</div>';
                            return;
                        }
                        
                        data.forEach(project => {
                            const item = document.createElement('a');
                            item.className = 'dropdown-item border-bottom py-2';
                            item.href = 'javascript:void(0)';
                            item.innerHTML = `
                                <div class="fw-bold text-primary">${project.project_number}</div>
                                <div class="text-dark">${project.project_name}</div>
                                <small class="text-muted"><i class="bi bi-person-fill"></i> Client: ${project.client_name ?? '-'}</small>
                            `;
                            item.addEventListener('click', () => selectProject(project));
                            searchResults.appendChild(item);
                        });
                    })
                    .catch(err => {
                        searchResults.innerHTML = '<div class="px-3 py-2 text-danger">Error fetching results.</div>';
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    function selectProject(project) {
        searchInput.value = project.project_name;
        searchResults.style.display = 'none';
        infoBlock.style.display = 'block';
        
        document.getElementById('infoProjectNumber').textContent = project.project_number;
        document.getElementById('infoProjectName').textContent = project.project_name;
        document.getElementById('infoClientName').textContent = project.client_name ?? '-';
        
        const formatDate = (dateString) => {
            if (!dateString) return '-';
            const d = new Date(dateString);
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        };
        
        document.getElementById('infoProjectPeriod').textContent = `${formatDate(project.start_date)} — ${formatDate(project.end_date)}`;
        
        const scurveContainer = document.getElementById('existingSCurveContainer');
        
        if (project.s_curves && project.s_curves.length > 0) {
            let scurveHtml = `<h6 class="font-weight-bold mb-3 text-primary border-bottom pb-2 text-start">EXISTING S-CURVE</h6>`;
            project.s_curves.forEach(sc => {
                scurveHtml += `
                    <div class="border rounded p-3 mb-2 bg-white text-start shadow-sm">
                        <h6 class="fw-bold mb-1">${sc.name}</h6>
                        <div class="small text-muted mb-3">
                            <i class="bi bi-calendar3"></i> ${formatDate(sc.start_date)} — ${formatDate(sc.end_date)}
                        </div>
                        <div>
                            <a href="/project-reports/s-curves/${sc.id}" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> VIEW</a>
                        </div>
                    </div>
                `;
            });
            scurveContainer.innerHTML = scurveHtml;
        } else {
            scurveContainer.innerHTML = `
                <div class="text-muted mb-3"><i class="bi bi-info-circle display-4 text-warning"></i><br><br>No S-Curve has been created for this project.</div>
                <div>
                    <a href="{{ route('s-curves.create') }}?project_id=${project.id}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> CREATE S-CURVE</a>
                </div>
            `;
        }
    }
});
</script>
@endsection
