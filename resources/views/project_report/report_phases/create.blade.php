@extends('layouts.app')
@section('title', 'Create Report Phase')
@section('page_title', 'Create Report Phase (BAPP)')

@push('styles')
<link href="{{ asset('vendor/summernote/summernote-lite.min.css') }}" rel="stylesheet">
<style>
    .note-editor .dropdown-toggle::after { all: unset; }
    .note-editor .dropdown-menu { min-width: 200px; }
</style>
@endpush

@section('header_actions')
    <a href="{{ route('report-phases.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 col-xl-8">
        <form action="{{ route('report-phases.store') }}" method="POST">
            @csrf
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Project</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Project <span class="text-danger">*</span></label>
                        <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->project_name }} - {{ $p->client_name }}</option>
                            @endforeach
                        </select>
                        @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Auto-filled Fields (Read-Only) -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name</label>
                            <input type="text" id="client_name" class="form-control" readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PR Number</label>
                            <input type="text" name="client_po_number" id="client_po_number" class="form-control" placeholder="Isi manual jika belum ada">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract Number (No Surat Perjanjian)</label>
                            <input type="text" name="contract_number" id="contract_number" class="form-control" placeholder="Isi manual jika belum ada">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PR Date</label>
                            <input type="text" id="client_po_date" class="form-control" readonly disabled>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Work Description</label>
                            <input type="text" id="project_name" class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Date <span class="text-danger">*</span></label>
                            <input type="date" name="document_date" class="form-control @error('document_date') is-invalid @enderror" value="{{ old('document_date', date('Y-m-d')) }}" required>
                            @error('document_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Progress Pekerjaan (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="progress_percentage" class="form-control @error('progress_percentage') is-invalid @enderror" min="0" max="100" step="0.01" value="{{ old('progress_percentage', '100') }}" required>
                                <span class="input-group-text">%</span>
                                @error('progress_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Narrative & Notes</h6>
                    <small class="text-muted">Gunakan placeholder untuk data dinamis.</small>
                </div>
                <div class="card-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Opening Paragraph</label>
                        <textarea name="opening_paragraph" class="form-control summernote">Pada hari ini, &#123;&#123; report_date &#125;&#125; telah diadakan pemeriksaan bersama atas pekerjaan:</textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Progress Paragraph</label>
                        <textarea name="progress_paragraph" class="form-control summernote">Dengan ini secara bersama-sama menyatakan bahwa pekerjaan telah mencapai progress &#123;&#123; progress_percentage &#125;&#125;% dengan baik sesuai dengan PO yang telah diterbitkan oleh &#123;&#123; client_name &#125;&#125;. Hitungan progress pekerjaan terlampir.</textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Closing Paragraph</label>
                        <textarea name="closing_paragraph" class="form-control summernote">Demikian Berita Acara Progress Pekerjaan ini kami buat dengan sebenarnya agar dapat digunakan sebagaimana mestinya.</textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea name="additional_notes" class="form-control summernote"></textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Signatures</h6>
                    <small class="text-muted">Isi nama dan jabatan untuk ditandatangani di BAPP.</small>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="font-weight-bold m-0">Client (Pihak Pertama)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addClientSignerBtn"><i class="bi bi-plus"></i> Add Signer</button>
                    </div>
                    
                    @for($i = 1; $i <= 4; $i++)
                    <div class="row client-signer-row" id="client_signer_row_{{ $i }}" style="{{ $i > 2 ? 'display:none;' : '' }}">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Signer {{ $i }} Name</label>
                            <input type="text" name="client_sign_name_{{ $i }}" class="form-control" value="{{ old('client_sign_name_'.$i) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Signer {{ $i }} Position</label>
                            <input type="text" name="client_sign_position_{{ $i }}" class="form-control" value="{{ old('client_sign_position_'.$i) }}">
                        </div>
                    </div>
                    @endfor

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">
                        <h6 class="font-weight-bold m-0">PT BIO PILAR UTAMA (Pihak Kedua)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addCompanySignerBtn"><i class="bi bi-plus"></i> Add Signer</button>
                    </div>

                    @for($i = 1; $i <= 4; $i++)
                    <div class="row company-signer-row" id="company_signer_row_{{ $i }}" style="{{ $i > 2 ? 'display:none;' : '' }}">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Signer {{ $i }} Name</label>
                            <input type="text" name="company_sign_name_{{ $i }}" class="form-control" value="{{ old('company_sign_name_'.$i) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Signer {{ $i }} Position</label>
                            <input type="text" name="company_sign_position_{{ $i }}" class="form-control" value="{{ old('company_sign_position_'.$i) }}">
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <div class="text-end mb-5">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Save Report Phase</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/summernote/summernote-lite.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    
    function fetchProjectDetails(id) {
        if (!id) {
            document.getElementById('client_name').value = '';
            document.getElementById('client_po_number').value = '';
            document.getElementById('contract_number').value = '';
            document.getElementById('client_po_date').value = '';
            document.getElementById('project_name').value = '';
            return;
        }

        fetch(`/report-phases/api/project-details/${id}`)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    document.getElementById('client_name').value = res.data.client_name || '-';
                    document.getElementById('client_po_number').value = res.data.client_po_number || '';
                    document.getElementById('contract_number').value = res.data.contract_number || '';
                    document.getElementById('client_po_date').value = res.data.client_po_date || '-';
                    document.getElementById('project_name').value = res.data.project_name || '-';
                }
            })
            .catch(err => console.error(err));
    }

    projectSelect.addEventListener('change', function() {
        fetchProjectDetails(this.value);
    });

    if (projectSelect.value) {
        fetchProjectDetails(projectSelect.value);
    }

    let clientSignerCount = 2;
    document.getElementById('addClientSignerBtn').addEventListener('click', function() {
        if (clientSignerCount < 4) {
            clientSignerCount++;
            document.getElementById('client_signer_row_' + clientSignerCount).style.display = 'flex';
            if (clientSignerCount === 4) {
                this.style.display = 'none';
            }
        }
    });

    let companySignerCount = 2;
    document.getElementById('addCompanySignerBtn').addEventListener('click', function() {
        if (companySignerCount < 4) {
            companySignerCount++;
            document.getElementById('company_signer_row_' + companySignerCount).style.display = 'flex';
            if (companySignerCount === 4) {
                this.style.display = 'none';
            }
        }
    });

    $('.summernote').summernote({
        height: 150,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    $('.insert-placeholder').click(function(e) {
        e.preventDefault();
        var placeholder = $(this).text();
        var editor = $(this).closest('.form-group').find('.summernote');
        editor.summernote('editor.insertText', placeholder);
    });
});
</script>
@endpush
