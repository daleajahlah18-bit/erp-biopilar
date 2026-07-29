@extends('layouts.app')
@section('title', 'Edit Report Phase')
@section('page_title', 'Edit Report Phase (BAPP)')

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
        <form action="{{ route('report-phases.update', $reportPhase->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Project</h6>
                    <span class="badge bg-secondary">{{ $reportPhase->report_number }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Project (Tidak dapat diubah)</label>
                        <select class="form-select" disabled>
                            <option>{{ $reportPhase->project->project_name }} - {{ $reportPhase->project->client_name }}</option>
                        </select>
                        <input type="hidden" name="project_id" value="{{ $reportPhase->project_id }}">
                    </div>

                    <!-- Auto-filled Fields (Read-Only) -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name</label>
                            <input type="text" class="form-control" value="{{ $reportPhase->project->client_name }}" readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PR Number</label>
                            <input type="text" name="client_po_number" class="form-control" value="{{ $reportPhase->project->client_po_number }}" placeholder="Isi manual jika belum ada">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract Number (No Surat Perjanjian)</label>
                            <input type="text" name="contract_number" class="form-control" value="{{ $reportPhase->project->contract_number }}" placeholder="Isi manual jika belum ada">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PR Date</label>
                            <input type="text" class="form-control" value="{{ $reportPhase->project->client_po_date ? $reportPhase->project->client_po_date->format('Y-m-d') : '' }}" readonly disabled>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Work Description</label>
                            <input type="text" class="form-control" value="{{ $reportPhase->project->project_name }}" readonly disabled>
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
                            <input type="date" name="document_date" class="form-control @error('document_date') is-invalid @enderror" value="{{ old('document_date', $reportPhase->document_date ? $reportPhase->document_date->format('Y-m-d') : '') }}" required>
                            @error('document_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Progress Pekerjaan (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="progress_percentage" class="form-control @error('progress_percentage') is-invalid @enderror" min="0" max="100" step="0.01" value="{{ old('progress_percentage', $reportPhase->progress_percentage) }}" required>
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
                        <textarea name="opening_paragraph" class="form-control summernote">{{ old('opening_paragraph', $reportPhase->opening_paragraph) }}</textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Progress Paragraph</label>
                        <textarea name="progress_paragraph" class="form-control summernote">{{ old('progress_paragraph', $reportPhase->progress_paragraph) }}</textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Closing Paragraph</label>
                        <textarea name="closing_paragraph" class="form-control summernote">{{ old('closing_paragraph', $reportPhase->closing_paragraph) }}</textarea>
                        @include('project_report.report_phases.partials._placeholders')
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea name="additional_notes" class="form-control summernote">{{ old('additional_notes', $reportPhase->additional_notes) }}</textarea>
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
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addClientSignerBtn" style="{{ ($reportPhase->client_sign_name_4 || old('client_sign_name_4')) ? 'display:none;' : '' }}"><i class="bi bi-plus"></i> Add Signer</button>
                    </div>

                    @for($i = 1; $i <= 4; $i++)
                        @php
                            $clientName = old('client_sign_name_'.$i, $reportPhase->{'client_sign_name_'.$i});
                            $showClient = ($i <= 2) || !empty($clientName) || !empty(old('client_sign_name_'.$i));
                            if ($i == 3 && (!empty($reportPhase->client_sign_name_4) || !empty(old('client_sign_name_4')))) $showClient = true;
                        @endphp
                        <div class="row client-signer-row" id="client_signer_row_{{ $i }}" style="{{ $showClient ? 'display:flex;' : 'display:none;' }}">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signer {{ $i }} Name</label>
                                <input type="text" name="client_sign_name_{{ $i }}" class="form-control" value="{{ $clientName }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signer {{ $i }} Position</label>
                                <input type="text" name="client_sign_position_{{ $i }}" class="form-control" value="{{ old('client_sign_position_'.$i, $reportPhase->{'client_sign_position_'.$i}) }}">
                            </div>
                        </div>
                    @endfor

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">
                        <h6 class="font-weight-bold m-0">PT BIO PILAR UTAMA (Pihak Kedua)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addCompanySignerBtn" style="{{ ($reportPhase->company_sign_name_4 || old('company_sign_name_4')) ? 'display:none;' : '' }}"><i class="bi bi-plus"></i> Add Signer</button>
                    </div>

                    @for($i = 1; $i <= 4; $i++)
                        @php
                            $companyName = old('company_sign_name_'.$i, $reportPhase->{'company_sign_name_'.$i});
                            $showCompany = ($i <= 2) || !empty($companyName) || !empty(old('company_sign_name_'.$i));
                            if ($i == 3 && (!empty($reportPhase->company_sign_name_4) || !empty(old('company_sign_name_4')))) $showCompany = true;
                        @endphp
                        <div class="row company-signer-row" id="company_signer_row_{{ $i }}" style="{{ $showCompany ? 'display:flex;' : 'display:none;' }}">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signer {{ $i }} Name</label>
                                <input type="text" name="company_sign_name_{{ $i }}" class="form-control" value="{{ $companyName }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signer {{ $i }} Position</label>
                                <input type="text" name="company_sign_position_{{ $i }}" class="form-control" value="{{ old('company_sign_position_'.$i, $reportPhase->{'company_sign_position_'.$i}) }}">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="text-end mb-5">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Update Report Phase</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/summernote/summernote-lite.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let clientSignerCount = 2;
    if (document.getElementById('client_signer_row_3').style.display !== 'none') clientSignerCount = 3;
    if (document.getElementById('client_signer_row_4').style.display !== 'none') clientSignerCount = 4;

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
    if (document.getElementById('company_signer_row_3').style.display !== 'none') companySignerCount = 3;
    if (document.getElementById('company_signer_row_4').style.display !== 'none') companySignerCount = 4;

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
