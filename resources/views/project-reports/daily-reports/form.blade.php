@extends('layouts.app')
@section('title', isset($dailyReport) ? 'Edit Daily Report' : 'Tambah Daily Report')
@section('page_title', isset($dailyReport) ? 'Edit Daily Report' : 'Tambah Daily Report')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ isset($dailyReport) ? route('daily-reports.update', $dailyReport) : route('daily-reports.store') }}" method="POST" enctype="multipart/form-data" id="dailyReportForm">
            @csrf
            @if(isset($dailyReport))
                @method('PUT')
            @endif

            <h5 class="mb-3 text-primary-custom border-bottom pb-2">Informasi Laporan</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required">Project</label>
                    <select name="project_id" class="form-select" required id="projectSelect">
                        <option value="">Pilih Project</option>
                        @foreach($projects as $proj)
                            @php
                                $isComplete = $proj->client_logo && $proj->field_of_work && $proj->work_package && $proj->client_user_name && $proj->executor_name && $proj->contract_number && $proj->project_address;
                                $missing = [];
                                if (!$proj->client_logo) $missing[] = 'Client Logo';
                                if (!$proj->field_of_work) $missing[] = 'Field of Work';
                                if (!$proj->work_package) $missing[] = 'Work Package';
                                if (!$proj->client_user_name) $missing[] = 'Client / User Name';
                                if (!$proj->executor_name) $missing[] = 'Executor';
                                if (!$proj->contract_number) $missing[] = 'Contract Number';
                                if (!$proj->project_address) $missing[] = 'Project Address';
                            @endphp
                            <option value="{{ $proj->id }}" 
                                data-complete="{{ $isComplete ? '1' : '0' }}"
                                data-missing="{{ implode(', ', $missing) }}"
                                {{ old('project_id', $dailyReport->project_id ?? '') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label required">Tanggal Laporan</label>
                    <input type="date" name="report_date" class="form-control" required value="{{ old('report_date', isset($dailyReport) ? $dailyReport->report_date->format('Y-m-d') : date('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label required">Cuaca</label>
                    <select name="weather" class="form-select" required>
                        <option value="Cerah" {{ old('weather', $dailyReport->weather ?? '') == 'Cerah' ? 'selected' : '' }}>☀ Cerah</option>
                        <option value="Mendung" {{ old('weather', $dailyReport->weather ?? '') == 'Mendung' ? 'selected' : '' }}>☁ Mendung</option>
                        <option value="Hujan" {{ old('weather', $dailyReport->weather ?? '') == 'Hujan' ? 'selected' : '' }}>🌧 Hujan</option>
                    </select>
                </div>
            </div>
            
            <div id="projectIdentityWarning" class="alert alert-danger" style="display: none;">
                <strong>Perhatian!</strong> Project ini belum memiliki identitas yang lengkap untuk Daily Report. <br>
                Data yang kurang: <span id="missingFieldsList"></span><br>
                Silakan lengkapi di menu Master Project terlebih dahulu. Anda tidak dapat menyimpan laporan ini.
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary-custom border-bottom pb-2">Man Power</h5>
                    <table class="table table-bordered" id="manpowerTable">
                        <thead class="table-light">
                            <tr>
                                <th>Posisi / Nama</th>
                                <th width="120">Jumlah</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody id="manpowerBody">
                            @if(isset($dailyReport) && $dailyReport->manpower->count() > 0)
                                @foreach($dailyReport->manpower as $i => $mp)
                                <tr>
                                    <td><input type="text" name="manpower[{{$i}}][position]" class="form-control" value="{{ $mp->position }}" required></td>
                                    <td><input type="number" name="manpower[{{$i}}][quantity]" class="form-control text-center" value="{{ $mp->quantity }}" required min="1"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="manpower[0][position]" class="form-control" required></td>
                                    <td><input type="number" name="manpower[0][quantity]" class="form-control text-center" required min="1" value="1"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><button type="button" class="btn btn-sm btn-outline-primary w-100" id="addManpower"><i class="bi bi-plus"></i> Tambah Man Power</button></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3 text-primary-custom border-bottom pb-2">Supply Material</h5>
                    <table class="table table-bordered" id="materialTable">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Material</th>
                                <th width="120">Volume</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody id="materialBody">
                            @if(isset($dailyReport) && $dailyReport->materials->count() > 0)
                                @foreach($dailyReport->materials as $i => $mat)
                                <tr>
                                    <td><input type="text" name="materials[{{$i}}][material_name]" class="form-control" value="{{ $mat->material_name }}"></td>
                                    <td><input type="number" step="0.01" name="materials[{{$i}}][volume]" class="form-control text-center" value="{{ $mat->volume }}" min="0"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="materials[0][material_name]" class="form-control"></td>
                                    <td><input type="number" step="0.01" name="materials[0][volume]" class="form-control text-center" min="0"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><button type="button" class="btn btn-sm btn-outline-primary w-100" id="addMaterial"><i class="bi bi-plus"></i> Tambah Material</button></td>
                            </tr>
                        </tfoot>
                    </table>

                    <h5 class="mb-3 mt-4 text-primary-custom border-bottom pb-2">Alat Kerja (Tools)</h5>
                    <table class="table table-bordered" id="toolTable">
                        <thead class="table-light">
                            <tr>
                                <th>Alat Kerja</th>
                                <th width="100">Jumlah</th>
                                <th width="100">Unit</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody id="toolBody">
                            @if(isset($dailyReport) && $dailyReport->tools->count() > 0)
                                @foreach($dailyReport->tools as $i => $tool)
                                <tr>
                                    <td><input type="text" name="tools[{{$i}}][tool_name]" class="form-control" value="{{ $tool->tool_name }}" required></td>
                                    <td><input type="number" name="tools[{{$i}}][quantity]" class="form-control text-center" value="{{ $tool->quantity }}" required min="1"></td>
                                    <td><input type="text" name="tools[{{$i}}][unit]" class="form-control" value="{{ $tool->unit }}" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="tools[0][tool_name]" class="form-control" required></td>
                                    <td><input type="number" name="tools[0][quantity]" class="form-control text-center" required min="1" value="1"></td>
                                    <td><input type="text" name="tools[0][unit]" class="form-control" required value="unit"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><button type="button" class="btn btn-sm btn-outline-primary w-100" id="addTool"><i class="bi bi-plus"></i> Tambah Alat Kerja</button></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <h5 class="mb-3 text-primary-custom border-bottom pb-2">Deskripsi & Catatan</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan yang Dilaksanakan</label>
                    <textarea name="work_description" class="form-control" rows="4">{{ old('work_description', $dailyReport->work_description ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Catatan dan Evaluasi</label>
                    <textarea name="evaluation_notes" class="form-control" rows="4">{{ old('evaluation_notes', $dailyReport->evaluation_notes ?? '') }}</textarea>
                </div>
            </div>

            <h5 class="mb-3 text-primary-custom border-bottom pb-2">Dokumentasi (Foto)</h5>
            <div class="mb-4">
                <div class="border rounded p-4 text-center bg-light" id="dropZone" style="border: 2px dashed #0c4a6e !important; cursor: pointer;">
                    <i class="bi bi-cloud-arrow-up display-4 text-primary-custom"></i>
                    <p class="mt-2 mb-0">Drag & Drop foto ke sini, atau klik untuk memilih file (Max 20 Foto)</p>
                    <input type="file" name="documentations[]" id="fileInput" multiple accept="image/*" class="d-none">
                </div>
                <div class="row mt-3" id="previewGallery">
                    @if(isset($dailyReport) && $dailyReport->documentations)
                        @foreach($dailyReport->documentations as $doc)
                            <div class="col-md-3 mb-3 existing-photo" data-id="{{ $doc->id }}">
                                <div class="card h-100 relative">
                                    <img src="{{ asset('storage/' . $doc->photo) }}" class="card-img-top" style="height:150px; object-fit:cover;">
                                    <div class="card-body p-2">
                                        <input type="text" class="form-control form-control-sm mb-2" value="{{ $doc->caption }}" disabled>
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-existing" data-id="{{ $doc->id }}">Hapus Foto</button>
                                    </div>
                                    <input type="hidden" name="existing_photos[]" value="{{ $doc->id }}">
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div id="deletedPhotosContainer"></div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('daily-reports.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary-custom"><i class="bi bi-save"></i> Simpan Laporan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Row Generators
    let mpIndex = 100, matIndex = 100, toolIndex = 100;

    document.getElementById('addManpower').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="manpower[${mpIndex}][position]" class="form-control" required></td>
            <td><input type="number" name="manpower[${mpIndex}][quantity]" class="form-control text-center" required min="1" value="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
        `;
        document.getElementById('manpowerBody').appendChild(tr);
        mpIndex++;
    });

    document.getElementById('addMaterial').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="materials[${matIndex}][material_name]" class="form-control"></td>
            <td><input type="number" step="0.01" name="materials[${matIndex}][volume]" class="form-control text-center" min="0"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
        `;
        document.getElementById('materialBody').appendChild(tr);
        matIndex++;
    });

    document.getElementById('addTool').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="tools[${toolIndex}][tool_name]" class="form-control" required></td>
            <td><input type="number" name="tools[${toolIndex}][quantity]" class="form-control text-center" required min="1" value="1"></td>
            <td><input type="text" name="tools[${toolIndex}][unit]" class="form-control" required value="unit"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
        `;
        document.getElementById('toolBody').appendChild(tr);
        toolIndex++;
    });

    // Remove row delegation
    document.addEventListener('click', function(e) {
        if(e.target.closest('.remove-row')) {
            const tr = e.target.closest('tr');
            const tbody = tr.parentElement;
            if (tbody.id === 'materialBody' || tbody.children.length > 1) {
                tr.remove();
            } else {
                notifyError('Pemberitahuan', 'Minimal harus ada 1 baris.');
            }
        }
        
        if (e.target.closest('.delete-existing')) {
            const btn = e.target.closest('.delete-existing');
            const id = btn.getAttribute('data-id');
            document.getElementById('deletedPhotosContainer').insertAdjacentHTML('beforeend', `<input type="hidden" name="deleted_photos[]" value="${id}">`);
            btn.closest('.existing-photo').remove();
        }
    });

    // Drag and Drop Logic
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const gallery = document.getElementById('previewGallery');
    let dataTransfer = new DataTransfer();

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#e0f2fe';
    });
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '';
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '';
        handleFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    let photoCounter = 0;

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if(!file.type.startsWith('image/')) return;
            dataTransfer.items.add(file);
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-3 new-photo';
                col.innerHTML = `
                    <div class="card h-100">
                        <img src="${e.target.result}" class="card-img-top" style="height:150px; object-fit:cover;">
                        <div class="card-body p-2">
                            <input type="text" name="captions[${photoCounter}]" class="form-control form-control-sm mb-2" placeholder="Caption foto...">
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-new-photo" data-index="${photoCounter}">Batal</button>
                        </div>
                    </div>
                `;
                gallery.appendChild(col);
                photoCounter++;
            };
            reader.readAsDataURL(file);
        });
        updateFileInput();
    }

    gallery.addEventListener('click', (e) => {
        if(e.target.classList.contains('remove-new-photo')) {
            const index = e.target.getAttribute('data-index');
            e.target.closest('.new-photo').remove();
            
            // Remove from dataTransfer
            const dt = new DataTransfer();
            Array.from(dataTransfer.files).forEach((file, i) => {
                if (i != index) dt.items.add(file);
            });
            dataTransfer = dt;
            updateFileInput();
        }
    });

    function updateFileInput() {
        fileInput.files = dataTransfer.files;
    }
    
    // Project Selection Validation
    const projectSelect = document.getElementById('projectSelect');
    const warningBox = document.getElementById('projectIdentityWarning');
    const missingFieldsList = document.getElementById('missingFieldsList');
    const submitBtn = document.querySelector('#dailyReportForm button[type="submit"]');
    
    function validateSelectedProject() {
        if (projectSelect.value === "") {
            warningBox.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }
        
        const selectedOption = projectSelect.options[projectSelect.selectedIndex];
        const isComplete = selectedOption.getAttribute('data-complete') === '1';
        
        if (!isComplete) {
            missingFieldsList.textContent = selectedOption.getAttribute('data-missing');
            warningBox.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            warningBox.style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    projectSelect.addEventListener('change', validateSelectedProject);
    validateSelectedProject(); // Run on load
});
</script>
@endsection
