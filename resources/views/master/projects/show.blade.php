@extends('layouts.app')
@section('title', 'Detail Project')
@section('page_title', 'Detail Project')
@section('header_actions')
<a href="{{ route('master.projects.index') }}" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Kembali</a>
<a href="{{ route('master.projects.edit', $project) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
@endsection

@section('content')

<ul class="nav nav-tabs mb-4" id="projectTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</button>
  </li>
</ul>

<div class="tab-content" id="projectTabsContent">
  <!-- OVERVIEW TAB -->
  <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Project Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($project->project_value, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fa-2x text-gray-300" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total HPP</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($project->hpp, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calculator fa-2x text-gray-300" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @php
            $margin = $project->margin;
            $marginPercentage = $project->margin_percentage;
            $marginColor = $margin > 0 ? 'success' : ($margin < 0 ? 'danger' : 'warning');
        @endphp

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $marginColor }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $marginColor }} text-uppercase mb-1">Margin</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up-arrow fa-2x text-gray-300" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Margin Percentage</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($marginPercentage, 2, ',', '.') }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-percent fa-2x text-gray-300" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Utama</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%" class="text-muted">Nama Project</td>
                            <td><strong>{{ $project->project_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Client Name</td>
                            <td>{{ $project->client_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($project->project_status == 'Completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($project->project_status == 'On Going')
                                    <span class="badge bg-primary">On Going</span>
                                @elseif($project->project_status == 'Cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Person In Charge (PIC)</td>
                            <td>{{ $project->person_in_charge }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Project Address</td>
                            <td>{{ $project->project_address ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Waktu & Timeline</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-muted">Client PR Number</td>
                            <td>{{ $project->client_po_number ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td width="40%" class="text-muted">Client PR Date</td>
                            <td>{{ $project->client_po_date ? $project->client_po_date->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Start Date</td>
                            <td>{{ $project->project_start_date ? $project->project_start_date->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">End Date</td>
                            <td>{{ $project->project_end_date ? $project->project_end_date->format('d F Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Schedule</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped m-0">
                            <thead class="">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="30%">TOP</th>
                                    <th width="15%" class="text-center">%</th>
                                    <th width="25%">Termin</th>
                                    <th width="25%" class="text-end">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->projectPaymentTerms ?? [] as $index => $term)
                                    @php
                                        $nominal = ($project->project_value * $term->percentage) / 100;
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle">{{ $term->top_type }}</td>
                                        <td class="text-center align-middle">{{ number_format($term->percentage, 2, ',', '.') }}%</td>
                                        <td class="align-middle">{{ $term->term_value }} {{ $term->term_unit }}</td>
                                        <td class="text-end align-middle fw-bold">Rp {{ number_format($nominal, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada data payment schedule</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(isset($project->projectPaymentTerms) && $project->projectPaymentTerms->count() > 0)
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Total:</th>
                                        <th class="text-center">100%</th>
                                        <th></th>
                                        <th class="text-end">Rp {{ number_format($project->project_value, 2, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- DOCUMENTS TAB -->
  <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Project Documents</h6>
              <div class="d-flex align-items-center">
                  <input type="text" id="searchDocName" class="form-control form-control-sm me-2" placeholder="Search Document...">
                  <select id="filterDocCategory" class="form-select form-select-sm me-2" style="width: 150px;">
                      <option value="">All Categories</option>
                      <option value="Contract">Contract</option>
                      <option value="Purchase">Purchase</option>
                      <option value="Drawing">Drawing</option>
                      <option value="Invoice">Invoice</option>
                      <option value="Payment">Payment</option>
                      <option value="Report">Report</option>
                      <option value="Photo">Photo</option>
                      <option value="Certificate">Certificate</option>
                      <option value="BAST">BAST</option>
                      <option value="Other">Other</option>
                  </select>
                  <button class="btn btn-sm btn-secondary me-2" id="btnFilterDocs"><i class="bi bi-search"></i></button>
                    <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="bi bi-upload"></i> Upload Document
                    </button>
                </div>
          </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0" id="documentsTable">
                    <thead class="">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                              <th>Document Name</th>
                              <th>Category</th>
                              <th>Version</th>
                              <th>Remarks</th>
                              <th>Uploaded By</th>
                              <th>Upload Date</th>
                              <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="uploadDocForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocModalLabel">Upload Project Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Document Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="document_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="document_category" required>
                        <option value="">Select Category...</option>
                        <option value="Contract">Contract</option>
                        <option value="Purchase">Purchase</option>
                        <option value="Drawing">Drawing</option>
                        <option value="Invoice">Invoice</option>
                        <option value="Payment">Payment</option>
                        <option value="Report">Report</option>
                        <option value="Photo">Photo</option>
                        <option value="Certificate">Certificate</option>
                        <option value="BAST">BAST</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Version</label>
                    <input type="text" class="form-control" name="version" placeholder="e.g. 1.0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" rows="2"></textarea>
                </div>
                  <div class="mb-3">
                      <label class="form-label">Google Drive Link <span class="text-danger">*</span></label>
                      <input type="url" class="form-control" name="google_drive_link" required placeholder="https://drive.google.com/...">
                  </div>
                  
                  <div class="progress d-none" id="uploadProgressContainer">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" id="uploadProgressBar">0%</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnUploadSubmit">Upload</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="height: 90vh;">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Document Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    const projectId = {{ $project->id }};
    const csrfToken = document.querySelector('input[name="_token"]').value;

    function loadDocuments() {
        const search = document.getElementById('searchDocName') ? document.getElementById('searchDocName').value : '';
        const category = document.getElementById('filterDocCategory') ? document.getElementById('filterDocCategory').value : '';
        
        fetch(`/project-documents?project_id=${projectId}&search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#documentsTable tbody');
                tbody.innerHTML = '';
                
                if(data.documents.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3 text-muted">No documents found.</td></tr>';
                    return;
                }

                data.documents.forEach((doc, index) => {
                    const row = `
                        <tr>
                            <td class="text-center align-middle">${index + 1}</td>
                            <td class="align-middle">
                                <strong>${doc.document_name}</strong>
                            </td>
                            <td class="align-middle"><span class="badge bg-secondary">${doc.document_category}</span></td>
                            <td class="align-middle">${doc.version || '-'}</td>
                            <td class="align-middle">${doc.remarks || '-'}</td>
                            <td class="align-middle">${doc.uploaded_by_name}</td>
                            <td class="align-middle">${doc.created_at_formatted}</td>
                            <td class="text-center align-middle">
                                <a href="${doc.google_drive_link}" target="_blank" class="btn btn-sm btn-info text-white me-1" title="Open Document">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${doc.id}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(err => console.error(err));
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadDocuments();
        
        if (document.getElementById('btnFilterDocs')) {
            document.getElementById('btnFilterDocs').addEventListener('click', loadDocuments);
        }
        if (document.getElementById('searchDocName')) {
            document.getElementById('searchDocName').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') loadDocuments();
            });
        }
        if (document.getElementById('filterDocCategory')) {
            document.getElementById('filterDocCategory').addEventListener('change', loadDocuments);
        }

        document.getElementById('uploadDocForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btnUploadSubmit');
            const progressContainer = document.getElementById('uploadProgressContainer');
            const progressBar = document.getElementById('uploadProgressBar');
            
            btnSubmit.disabled = true;
            progressContainer.classList.remove('d-none');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';

            const formData = new FormData(this);
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('project-documents.store') }}', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');
            
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = percentComplete + '%';
                }
            };
            
            xhr.onload = function() {
                btnSubmit.disabled = false;
                progressContainer.classList.add('d-none');
                
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (xhr.status === 200 && res.success) {
                        notifyError('Pemberitahuan', 'Document uploaded successfully!');
                        
                        // close modal
                        const modalEl = document.getElementById('uploadDocModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                        
                        document.getElementById('uploadDocForm').reset();
                        loadDocuments();
                    } else {
                        let errStr = res.message || 'Upload failed!';
                        if (res.errors) {
                            errStr += '\n' + Object.values(res.errors).flat().join('\n');
                        }
                        notifyError('Terjadi Kesalahan', errStr);
                    }
                } catch (e) {
                    console.error("Parse Error:", xhr.responseText);
                    notifyError('Pemberitahuan', "A server error occurred during upload. Please try again.");
                }
            };
            
            xhr.onerror = function() {
                btnSubmit.disabled = false;
                progressContainer.classList.add('d-none');
                notifyError('Pemberitahuan', 'An error occurred during upload.');
            };
            
            xhr.send(formData);
        });

        document.querySelector('#documentsTable').addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                const btn = e.target.closest('.btn-delete');
                const docId = btn.getAttribute('data-id');
                
                confirmDelete(() => {
                    fetch(`/project-documents/${docId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            notifySuccess('Berhasil!', 'Dokumen berhasil dihapus.');
                            loadDocuments();
                        } else {
                            notifyError('Terjadi Kesalahan', data.message || 'Failed to delete document');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        notifyError('Pemberitahuan', 'Error deleting document');
                    });
                }, 'Hapus Dokumen?', 'Dokumen yang dihapus tidak dapat dikembalikan.');
            }
        });
    });
</script>
@endpush
