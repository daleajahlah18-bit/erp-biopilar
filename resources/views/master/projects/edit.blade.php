@extends('layouts.app')
@section('title', 'Edit Project')
@section('page_title', 'Edit Project')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Project</h6>
                <span class="badge bg-secondary">{{ $project->project_number ?? 'ID: '.$project->id }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('master.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="project_name" class="form-control @error('project_name') is-invalid @enderror" value="{{ old('project_name', $project->project_name) }}" required>
                            @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name', $project->client_name) }}" required>
                            @error('client_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Person In Charge (PIC) <span class="text-danger">*</span></label>
                            <input type="text" name="person_in_charge" class="form-control @error('person_in_charge') is-invalid @enderror" value="{{ old('person_in_charge', $project->person_in_charge) }}" required>
                            @error('person_in_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="project_status" class="form-select @error('project_status') is-invalid @enderror" required>
                                <option value="Draft" {{ old('project_status', $project->project_status) == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="On Going" {{ old('project_status', $project->project_status) == 'On Going' ? 'selected' : '' }}>On Going</option>
                                <option value="Completed" {{ old('project_status', $project->project_status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Cancelled" {{ old('project_status', $project->project_status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('project_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Address</label>
                        <textarea name="project_address" class="form-control @error('project_address') is-invalid @enderror" rows="2">{{ old('project_address', $project->project_address) }}</textarea>
                        @error('project_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Client PR Date</label>
                            <input type="date" name="client_po_date" class="form-control @error('client_po_date') is-invalid @enderror" value="{{ old('client_po_date', $project->client_po_date ? $project->client_po_date->format('Y-m-d') : '') }}">
                            @error('client_po_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Project Start Date</label>
                            <input type="date" name="project_start_date" class="form-control @error('project_start_date') is-invalid @enderror" value="{{ old('project_start_date', $project->project_start_date ? $project->project_start_date->format('Y-m-d') : '') }}">
                            @error('project_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Project End Date</label>
                            <input type="date" name="project_end_date" class="form-control @error('project_end_date') is-invalid @enderror" value="{{ old('project_end_date', $project->project_end_date ? $project->project_end_date->format('Y-m-d') : '') }}">
                            @error('project_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Project Value (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="project_value" name="project_value" class="form-control @error('project_value') is-invalid @enderror" value="{{ old('project_value', $project->project_value) }}" min="1" step="0.01" required>
                            @error('project_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <hr>
                    <div class="d-flex justify-content-between mb-3 align-items-center mt-4">
                        <h6 class="text-primary font-weight-bold m-0">PROJECT IDENTITY (For Daily Report)</h6>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Client Logo <small class="text-muted">(Max 2MB)</small></label>
                            @if($project->client_logo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$project->client_logo) }}" alt="Logo" style="height: 50px;">
                                </div>
                            @endif
                            <input type="file" name="client_logo" class="form-control @error('client_logo') is-invalid @enderror" accept="image/*">
                            @error('client_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Field of Work</label>
                            <input type="text" name="field_of_work" class="form-control @error('field_of_work') is-invalid @enderror" value="{{ old('field_of_work', $project->field_of_work) }}">
                            @error('field_of_work') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">PR Number</label>
                            <input type="text" name="client_po_number" class="form-control @error('client_po_number') is-invalid @enderror" value="{{ old('client_po_number', $project->client_po_number) }}">
                            @error('client_po_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client / User Name</label>
                            <input type="text" name="client_user_name" class="form-control @error('client_user_name') is-invalid @enderror" value="{{ old('client_user_name', $project->client_user_name) }}">
                            @error('client_user_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Executor</label>
                            <input type="text" name="executor_name" class="form-control @error('executor_name') is-invalid @enderror" value="{{ old('executor_name', $project->executor_name) }}">
                            @error('executor_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contract Number</label>
                            <input type="text" name="contract_number" class="form-control @error('contract_number') is-invalid @enderror" value="{{ old('contract_number', $project->contract_number) }}">
                            @error('contract_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between mb-3 align-items-center mt-4">
                        <h6 class="text-primary font-weight-bold m-0">PAYMENT SCHEDULE</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddTermin"><i class="bi bi-plus-lg"></i> Add Termin</button>
                    </div>
                    
                    <div id="paymentWarning" class="alert alert-warning" style="display:none;">
                        Silakan isi Project Value terlebih dahulu sebelum menambahkan Payment Schedule.
                    </div>

                    @error('payment_terms') <div class="alert alert-danger">{{ $message }}</div> @enderror

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">TOP</th>
                                    <th width="15%">%</th>
                                    <th width="25%">Termin</th>
                                    <th width="20%">Nominal (Rp)</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="paymentTbody">
                                @foreach($project->projectPaymentTerms ?? [] as $index => $term)
                                <tr class="payment-row">
                                    <td class="row-num text-center align-middle">{{ $index + 1 }}</td>
                                    <td>
                                        <select name="payment_terms[{{ $index }}][top_type]" class="form-select top-select" data-selected="{{ $term->top_type }}" required>
                                            <option value="">-- Pilih TOP --</option>
                                            <option value="Down Payment" {{ $term->top_type == 'Down Payment' ? 'selected' : '' }}>Down Payment</option>
                                            <option value="After Progress 50%" {{ $term->top_type == 'After Progress 50%' ? 'selected' : '' }}>After Progress 50%</option>
                                            <option value="After Progress 60%" {{ $term->top_type == 'After Progress 60%' ? 'selected' : '' }}>After Progress 60%</option>
                                            <option value="After Progress 100%" {{ $term->top_type == 'After Progress 100%' ? 'selected' : '' }}>After Progress 100%</option>
                                            <option value="Monthly Progress" {{ $term->top_type == 'Monthly Progress' ? 'selected' : '' }}>Monthly Progress</option>
                                            <option value="Material On Site" {{ $term->top_type == 'Material On Site' ? 'selected' : '' }}>Material On Site</option>
                                            <option value="After Installation" {{ $term->top_type == 'After Installation' ? 'selected' : '' }}>After Installation</option>
                                            <option value="Retention" {{ $term->top_type == 'Retention' ? 'selected' : '' }}>Retention</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="payment_terms[{{ $index }}][percentage]" class="form-control pct-input" min="0.01" max="100" step="0.01" value="{{ $term->percentage }}" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="payment_terms[{{ $index }}][term_value]" class="form-control" min="0" value="{{ $term->term_value }}" required>
                                            <select name="payment_terms[{{ $index }}][term_unit]" class="form-select" style="max-width: 90px;" required>
                                                <option value="Days" {{ $term->term_unit == 'Days' ? 'selected' : '' }}>Days</option>
                                                <option value="Months" {{ $term->term_unit == 'Months' ? 'selected' : '' }}>Months</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="text-end align-middle fw-bold nominal-display">
                                        0,00
                                    </td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total:</th>
                                    <th id="totalPercentage">0%</th>
                                    <th></th>
                                    <th id="totalNominal">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold mb-3">Financial Information (Read-Only)</h6>
                    <p class="text-muted small mb-3">HPP dan Margin dihitung berdasarkan Project Fabrication.</p>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">HPP (Cost)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control " value="{{ number_format((float)$project->hpp, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Margin</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                @php
                                    $marginClass = 'text-warning';
                                    if ($project->margin > 0) $marginClass = 'text-success';
                                    if ($project->margin < 0) $marginClass = 'text-danger';
                                @endphp
                                <input type="text" class="form-control  fw-bold {{ $marginClass }}" value="{{ number_format((float)$project->margin, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Margin %</label>
                            <div class="input-group">
                                <input type="text" class="form-control  fw-bold {{ $marginClass }}" value="{{ number_format((float)$project->margin_percentage, 2, ',', '.') }}" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('master.projects.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="topOptions" style="display:none;">
    <option value="">-- Pilih TOP --</option>
    <option value="Down Payment">Down Payment</option>
    <option value="After Progress 50%">After Progress 50%</option>
    <option value="After Progress 60%">After Progress 60%</option>
    <option value="After Progress 100%">After Progress 100%</option>
    <option value="Monthly Progress">Monthly Progress</option>
    <option value="Material On Site">Material On Site</option>
    <option value="After Installation">After Installation</option>
    <option value="Retention">Retention</option>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.getElementById("paymentTbody");
    const btnAdd = document.getElementById("btnAddTermin");
    const projectValueInput = document.getElementById("project_value");
    const paymentWarning = document.getElementById("paymentWarning");
    const optionsHtml = document.getElementById("topOptions").innerHTML;
    let rowIndex = {{ $project->projectPaymentTerms ? $project->projectPaymentTerms->count() : 0 }};

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}).format(num);
    }

    function checkProjectValue() {
        const val = parseFloat(projectValueInput.value) || 0;
        if (val <= 0) {
            paymentWarning.style.display = 'block';
            btnAdd.disabled = true;
        } else {
            paymentWarning.style.display = 'none';
            btnAdd.disabled = false;
        }
        calculateAll();
    }

    function addRow() {
        const val = parseFloat(projectValueInput.value) || 0;
        if (val <= 0) {
            notifyError('Pemberitahuan', "Silakan isi Project Value terlebih dahulu!");
            return;
        }

        const tr = document.createElement("tr");
        tr.className = "payment-row";
        tr.innerHTML = `
            <td class="row-num text-center align-middle">${rowIndex + 1}</td>
            <td>
                <select name="payment_terms[${rowIndex}][top_type]" class="form-select top-select" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="payment_terms[${rowIndex}][percentage]" class="form-control pct-input" min="0.01" max="100" step="0.01" required value="0">
                    <span class="input-group-text">%</span>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="payment_terms[${rowIndex}][term_value]" class="form-control" min="0" required>
                    <select name="payment_terms[${rowIndex}][term_unit]" class="form-select" style="max-width: 90px;" required>
                        <option value="Days">Days</option>
                        <option value="Months">Months</option>
                    </select>
                </div>
            </td>
            <td class="text-end align-middle fw-bold nominal-display">
                0,00
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        updateRowNumbers();
    }

    function updateRowNumbers() {
        document.querySelectorAll(".payment-row .row-num").forEach((td, idx) => {
            td.textContent = idx + 1;
        });
    }

    function calculateAll() {
        let totalPct = 0;
        let totalNominal = 0;
        const projectValue = parseFloat(projectValueInput.value) || 0;

        document.querySelectorAll(".payment-row").forEach(row => {
            const pct = parseFloat(row.querySelector(".pct-input").value) || 0;
            const nominal = (projectValue * pct) / 100;
            
            row.querySelector(".nominal-display").textContent = formatRupiah(nominal);
            
            totalPct += pct;
            totalNominal += nominal;
        });

        document.getElementById("totalPercentage").textContent = totalPct.toFixed(2) + "%";
        document.getElementById("totalNominal").textContent = formatRupiah(totalNominal);
        
        if(Math.abs(totalPct - 100) < 0.01) {
            document.getElementById("totalPercentage").className = "text-success fw-bold";
        } else {
            document.getElementById("totalPercentage").className = "text-danger fw-bold";
        }
    }

    projectValueInput.addEventListener("input", checkProjectValue);
    btnAdd.addEventListener("click", addRow);

    tbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("pct-input")) {
            calculateAll();
        }
    });

    tbody.addEventListener("click", function(e) {
        if(e.target.closest(".btn-remove")) {
            e.target.closest("tr").remove();
            updateRowNumbers();
            calculateAll();
        }
    });

    checkProjectValue();
});
</script>
@endpush
