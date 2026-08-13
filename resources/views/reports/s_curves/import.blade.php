@extends('layouts.app')
@section('title', 'Import S-Curve')
@section('page_title', 'Import Excel S-Curve')

@section('content')
@if(session('error')) <div class="alert alert-danger">{!! nl2br(e(session('error'))) !!}</div> @endif

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm mb-4 border-left-primary">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Step 1 — Select Project & Upload Excel</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('s-curves.import.analyze') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    <div class="mb-4 position-relative">
                        <label class="form-label text-muted small fw-bold">Search Project</label>
                        <div style="position: relative;">
                            <i class="bi bi-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                            <input type="text" id="projectSearchInput" class="form-control" style="padding-left: 40px;" placeholder="Search project name / project number / client name..." autocomplete="off">
                        </div>
                        <div id="projectSearchResults" class="dropdown-menu w-100 shadow-sm" style="max-height: 300px; overflow-y: auto; display: none; position: absolute; z-index: 1000; top: 100%;"></div>
                    </div>

                    <div id="projectInfoBlock" class="mb-4 p-3 border rounded bg-light" style="display: none;">
                        <input type="hidden" name="project_id" id="hiddenProjectId">
                        <h6 class="font-weight-bold mb-3 text-primary border-bottom pb-2">PROJECT INFORMATION</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Project Number</small>
                                <span id="infoProjectNumber" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Project Name</small>
                                <span id="infoProjectName" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Client</small>
                                <span id="infoClientName" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Project Period</small>
                                <span id="infoProjectPeriod" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Project Duration</small>
                                <span id="infoProjectDuration" class="fw-bold text-success">-</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 border-top pt-3 text-center">
                            <h6 class="text-muted mb-3">Download the official S-Curve template for this project:</h6>
                            <a href="#" id="downloadTemplateBtn" class="btn btn-outline-success font-weight-bold">
                                <i class="bi bi-file-earmark-excel"></i> Download S-Curve Template
                            </a>
                        </div>
                        
                        <div class="row mt-4 border-top pt-3">
                            <div class="col-12">
                                <label class="form-label font-weight-bold">S-Curve Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="scurveName" class="form-control" placeholder="Contoh: Kurva S Tahap 1" required>
                                <small class="text-muted">You can edit the default name if needed.</small>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label font-weight-bold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="scurveStart" class="form-control" required>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label font-weight-bold">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="scurveEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div id="uploadBlock" style="display: none;">
                        <div class="mb-4 p-4 border border-primary rounded text-center bg-white" style="border-style: dashed !important;">
                            <label class="form-label font-weight-bold d-block mb-3">Upload Excel File (.xlsx)</label>
                            <input type="file" name="excel_file" id="excel_file" class="form-control w-50 mx-auto" accept=".xlsx,.xls" required>
                            <div class="text-muted small mt-2">Supported files: .xlsx, .xls. Ensure it contains WBS, Bobot, and Plan data.</div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('s-curves.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="analyzeBtn" onclick="return validateImportForm()"><i class="bi bi-file-earmark-bar-graph"></i> Analyze Excel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout = null;
    const searchInput = document.getElementById('projectSearchInput');
    const searchResults = document.getElementById('projectSearchResults');
    const infoBlock = document.getElementById('projectInfoBlock');
    const uploadBlock = document.getElementById('uploadBlock');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            infoBlock.style.display = 'none';
            uploadBlock.style.display = 'none';
            return;
        }
        
        searchResults.innerHTML = '<div class="px-3 py-2 text-muted small"><i class="spinner-border spinner-border-sm me-2"></i> Searching...</div>';
        searchResults.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('s-curves.projects.search') }}?q=${encodeURIComponent(query)}`)
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
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    function selectProject(project) {
        searchInput.value = project.project_name;
        searchResults.style.display = 'none';
        infoBlock.style.display = 'block';
        uploadBlock.style.display = 'block';
        
        document.getElementById('hiddenProjectId').value = project.id;
        document.getElementById('infoProjectNumber').textContent = project.project_number;
        document.getElementById('infoProjectName').textContent = project.project_name;
        document.getElementById('infoClientName').textContent = project.client_name ?? '-';
        
        const formatDateText = (dateString) => {
            if (!dateString) return '-';
            const d = new Date(dateString);
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        };
        const formatDateInput = (dateString) => {
            if (!dateString) return '';
            const d = new Date(dateString);
            const offset = d.getTimezoneOffset()
            d = new Date(d.getTime() - (offset*60*1000))
            return d.toISOString().split('T')[0]
        };
        
        document.getElementById('infoProjectPeriod').textContent = `${formatDateText(project.start_date)} — ${formatDateText(project.end_date)}`;
        
        let durationWeeks = 0;
        if (project.start_date && project.end_date) {
            const start = new Date(project.start_date);
            const end = new Date(project.end_date);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            durationWeeks = Math.ceil(diffDays / 7) || 1;
        }
        
        document.getElementById('infoProjectDuration').textContent = durationWeeks + ' Weeks';
        
        const templateBtn = document.getElementById('downloadTemplateBtn');
        templateBtn.href = `{{ url('s-curves/import/template') }}/${project.id}`;
        
        document.getElementById('scurveName').value = 'Kurva S - ' + project.project_name;
        document.getElementById('scurveStart').value = project.start_date ? project.start_date.split(' ')[0] : '';
        document.getElementById('scurveEnd').value = project.end_date ? project.end_date.split(' ')[0] : '';
    }
});

function validateImportForm() {
    const file = document.getElementById('excel_file').value;
    const pid = document.getElementById('hiddenProjectId').value;
    
    if(!pid) {
        alert("Pilih project terlebih dahulu.");
        return false;
    }
    
    if (!file) {
        alert("Pilih file Excel untuk diupload.");
        return false;
    }

    const btn = document.getElementById('analyzeBtn');
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Analyzing...';
    btn.disabled = true;
    
    document.getElementById('importForm').submit();
    return true;
}
</script>
@endsection
