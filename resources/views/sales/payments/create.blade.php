@extends("layouts.app")
@section("title", "Terima Sales Payment (Termin)")
@section("page_title", "Sales Payment")
@section("page_subtitle", "Terima Pembayaran Termin")

@section("content")
<div class="card mb-4">
    <div class="card-body">
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Pilih Invoice *</label>
                <select name="sales_invoice_id" id="sales_invoice_id" class="form-select">
                    <option value="">-- Pilih Invoice --</option>
                    @foreach($invoices as $inv)
                        @php
                            $clientName = $inv->salesOrder->project->client_name ?? ($inv->salesOrder->customer->customer_name ?? '-');
                        @endphp
                        <option value="{{ $inv->id }}">{{ $inv->invoice_number }} ({{ $clientName }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row  p-3 rounded mb-3 d-none" id="project_info_card">
            <div class="col-md-3">
                <label class="text-muted">Customer</label>
                <h6 id="display_customer">-</h6>
            </div>
            <div class="col-md-3">
                <label class="text-muted">Project Name</label>
                <h6 id="display_project">-</h6>
            </div>
            <div class="col-md-3">
                <label class="text-muted">Sales Order Number</label>
                <h6 id="display_so">-</h6>
            </div>
            <div class="col-md-3">
                <label class="text-muted">Project Value</label>
                <h6 id="display_project_value" class="text-primary">-</h6>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 d-none" id="payment_schedule_card">
    <div class="card-header  py-3">
        <h6 class="m-0 font-weight-bold text-primary">Payment Schedule (Termin)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped m-0">
                <thead class="">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>TOP</th>
                        <th class="text-center">%</th>
                        <th>Termin</th>
                        <th class="text-end">Nominal (Rp)</th>
                        <th class="text-end">Sudah Dibayar (Rp)</th>
                        <th class="text-end text-danger">Sisa Tagihan (Rp)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="terms_table_body">
                    <tr>
                        <td colspan="9" class="text-center text-muted">Pilih invoice terlebih dahulu</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Bayar -->
<div class="modal fade" id="modalBayar" tabindex="-1" aria-labelledby="modalBayarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBayarLabel">Proses Pembayaran Termin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('sales.payments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="sales_invoice_id" id="modal_sales_invoice_id">
                    <input type="hidden" name="project_payment_term_id" id="modal_term_id">
                    
                    <div class="mb-3">
                        <label class="text-muted">Tipe TOP</label>
                        <h6 id="modal_top_type" class="fw-bold">-</h6>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted">Nominal Termin</label>
                            <h6 id="modal_nominal">-</h6>
                        </div>
                        <div class="col-6">
                            <label class="text-muted text-success">Sudah Dibayar</label>
                            <h6 id="modal_paid" class="text-success">-</h6>
                        </div>
                    </div>
                    
                    <div class="mb-3 p-2 bg-danger text-white rounded">
                        <label>Sisa Tagihan</label>
                        <h5 id="modal_remaining" class="m-0">-</h5>
                    </div>
                    
                    <div class="mb-3">
                        <label>Nominal Pembayaran (Rp) *</label>
                        <input type="number" name="payment_amount" id="modal_payment_amount" class="form-control fs-4 fw-bold text-success" required min="1">
                        <small class="text-muted">Maksimal: <span id="modal_max_text">-</span></small>
                    </div>

                    <div class="mb-3">
                        <label>Tanggal Bayar *</label>
                        <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label>Metode Pembayaran *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Cash">Cash</option>
                            <option value="Giro">Giro</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Keterangan / Referensi</label>
                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Transfer BCA a/n Budi">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const invSelect = document.getElementById("sales_invoice_id");
    
    // Elements
    const projectInfoCard = document.getElementById("project_info_card");
    const paymentScheduleCard = document.getElementById("payment_schedule_card");
    const termsTbody = document.getElementById("terms_table_body");

    function formatRp(angka) {
        return "Rp " + parseFloat(angka).toLocaleString("id-ID", {minimumFractionDigits:2});
    }

    invSelect.addEventListener("change", function() {
        const invId = this.value;
        if(!invId) {
            projectInfoCard.classList.add("d-none");
            paymentScheduleCard.classList.add("d-none");
            return;
        }

        fetch(`/sales/payments/api/invoice-info/${invId}`)
        .then(res => res.json())
        .then(data => {
            // Show cards
            projectInfoCard.classList.remove("d-none");
            paymentScheduleCard.classList.remove("d-none");

            // Fill header info
            document.getElementById("display_customer").textContent = data.customer_name;
            document.getElementById("display_project").textContent = data.project_name;
            document.getElementById("display_so").textContent = data.sales_order_number;
            document.getElementById("display_project_value").textContent = formatRp(data.project_value);
            
            // Build table
            termsTbody.innerHTML = "";
            
            if (!data.terms || data.terms.length === 0) {
                termsTbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted">Tidak ada termin untuk project ini</td></tr>`;
                return;
            }

            data.terms.forEach((term, index) => {
                let statusBadge = "";
                if (term.status == 'Paid') statusBadge = '<span class="badge bg-success">Paid</span>';
                else if (term.status == 'Partially Paid') statusBadge = '<span class="badge bg-warning ">Partially Paid</span>';
                else statusBadge = '<span class="badge bg-danger">Unpaid</span>';

                let actionBtn = "";
                if (term.remaining_amount > 0) {
                    actionBtn = `<button class="btn btn-sm btn-primary btn-bayar" 
                        data-id="${term.id}"
                        data-top="${term.top_type}"
                        data-nominal="${term.nominal}"
                        data-paid="${term.total_paid}"
                        data-sisa="${term.remaining_amount}">
                        <i class="bi bi-wallet2"></i> Bayar
                    </button>`;
                } else {
                    actionBtn = `<button class="btn btn-sm btn-secondary" disabled>Lunas</button>`;
                }

                termsTbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle fw-bold">${term.top_type}</td>
                        <td class="text-center align-middle">${parseFloat(term.percentage).toLocaleString("id-ID")}%</td>
                        <td class="align-middle">${term.term_value} ${term.term_unit}</td>
                        <td class="text-end align-middle">${formatRp(term.nominal)}</td>
                        <td class="text-end align-middle text-success">${formatRp(term.total_paid)}</td>
                        <td class="text-end align-middle text-danger fw-bold">${formatRp(term.remaining_amount)}</td>
                        <td class="text-center align-middle">${statusBadge}</td>
                        <td class="text-center align-middle">${actionBtn}</td>
                    </tr>
                `;
            });

            // Bind click events to bayar buttons
            document.querySelectorAll('.btn-bayar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const termId = this.getAttribute('data-id');
                    const topType = this.getAttribute('data-top');
                    const nominal = parseFloat(this.getAttribute('data-nominal'));
                    const paid = parseFloat(this.getAttribute('data-paid'));
                    const sisa = parseFloat(this.getAttribute('data-sisa'));

                    document.getElementById('modal_sales_invoice_id').value = invId;
                    document.getElementById('modal_term_id').value = termId;
                    
                    document.getElementById('modal_top_type').textContent = topType;
                    document.getElementById('modal_nominal').textContent = formatRp(nominal);
                    document.getElementById('modal_paid').textContent = formatRp(paid);
                    document.getElementById('modal_remaining').textContent = formatRp(sisa);
                    
                    const paymentInput = document.getElementById('modal_payment_amount');
                    paymentInput.value = sisa;
                    paymentInput.max = sisa;
                    document.getElementById('modal_max_text').textContent = formatRp(sisa);

                    const modal = new bootstrap.Modal(document.getElementById('modalBayar'));
                    modal.show();
                });
            });

        })
        .catch(err => {
            console.error(err);
            notifyError('Pemberitahuan', "Gagal memuat data termin.");
        });
    });
});
</script>
@endpush
