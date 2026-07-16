@extends("layouts.app")
@section("title", "Buat Sales Order")
@section("page_title", "Sales Order")
@section("page_subtitle", "Buat Order Baru")

@section("content")
<div class="card shadow-sm">
    <div class="card-body">
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        <div class="alert alert-info mt-3 mb-0">
            <i class="bi bi-info-circle me-1"></i> Detail Produk dan Detail Jasa bersifat opsional. Minimal salah satu harus diisi.
        </div>
        <form action="{{ route("sales.orders.store") }}" method="POST" id="soForm">
            @csrf
            
            <h6 class="text-primary font-weight-bold mb-3">Informasi Order</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>No Order</label>
                    <input type="text" class="form-control " readonly placeholder="Auto Generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tanggal *</label>
                    <input type="date" name="sales_order_date" class="form-control" required value="{{ date("Y-m-d") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Project *</label>
                    <select name="project_id" id="projectSelect" class="form-select" required>
                        <option value="">-- Pilih Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Keterangan / Notes</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>

            <!-- Project Info Display -->
            <div id="projectInfo" class=" p-3 rounded mb-4" style="display:none;">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Client Name</small>
                        <strong id="infoClientName">-</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Client PR Date</small>
                        <strong id="infoClientPoDate">-</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">PO Value</small>
                        <strong id="infoPoValue" class="text-success">-</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Terms of Payment</small>
                        <strong id="infoTerms">-</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Status</small>
                        <strong id="infoStatus">-</strong>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between mb-3 align-items-center">
                <h6 class="text-primary font-weight-bold m-0">Detail Produk</h6>
                <button type="button" class="btn btn-sm btn-primary" id="btnAddItem"><i class="bi bi-plus-lg"></i> Tambah Produk</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="">
                        <tr>
                            <th width="30%">Produk</th>
                            <th width="10%">Stok</th>
                            <th width="10%">Qty</th>
                            <th width="10%">Unit</th>
                            <th width="15%">Harga (Rp)</th>
                            <th width="20%">Subtotal (Rp)</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody"></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mb-3 align-items-center mt-4">
                <h6 class="text-primary font-weight-bold m-0">Detail Jasa</h6>
                <button type="button" class="btn btn-sm btn-primary" id="btnAddService"><i class="bi bi-plus-lg"></i> Tambah Jasa</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="">
                        <tr>
                            <th width="30%">Nama Jasa</th>
                            <th width="10%">Qty</th>
                            <th width="15%">Harga (Rp)</th>
                            <th width="20%">Subtotal (Rp)</th>
                            <th width="20%">Keterangan</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="servicesTbody"></tbody>
                </table>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-end fw-bold align-middle">Total Produk:</td>
                            <td class="text-end fw-bold fs-6" id="totalProdukDisplay">0,00</td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold align-middle">Total Jasa:</td>
                            <td class="text-end fw-bold fs-6" id="totalJasaDisplay">0,00</td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold align-middle border-top">Grand Total:</td>
                            <td class="text-end fw-bold text-primary fs-4 border-top" id="totalDisplay">0,00</td>
                        </tr>
                    </table>
                </div>
            </div>

            <input type="hidden" name="total_amount" id="totalInput" value="0">

            <div class="text-end mt-4">
                <a href="{{ route('sales.orders.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan Order</button>
            </div>
        </form>
    </div>
</div>

<div id="productOptions" style="display:none;">
    <option value="">-- Pilih --</option>
    @foreach($products as $p)
        <option value="{{ $p->id }}">{{ $p->product_name }} ({{ str_replace('_', ' ', $p->product_type) }})</option>
    @endforeach
</div>
@endsection

@push("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.getElementById("itemsTbody");
    const servicesTbody = document.getElementById("servicesTbody");
    const btnAdd = document.getElementById("btnAddItem");
    const btnAddService = document.getElementById("btnAddService");
    const optionsHtml = document.getElementById("productOptions").innerHTML;
    const projectSelect = document.getElementById("projectSelect");
    let rowIndex = 0;
    let serviceIndex = 0;

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', {minimumFractionDigits:0, maximumFractionDigits:0}).format(num);
    }

    // Load Project Info
    projectSelect.addEventListener("change", function(e) {
        const pid = e.target.value;
        const infoDiv = document.getElementById("projectInfo");
        if(!pid) {
            infoDiv.style.display = 'none';
            return;
        }

        fetch(`/sales/orders/api/project-info/${pid}`)
        .then(res => res.json())
        .then(data => {
            if(data.error) return;
            document.getElementById("infoClientName").textContent = data.client_name || '-';
            document.getElementById("infoClientPoDate").textContent = data.client_po_date || '-';
            document.getElementById("infoPoValue").textContent = 'Rp ' + formatRupiah(data.po_amount || 0);
            document.getElementById("infoTerms").textContent = data.terms_of_payment || '-';
            document.getElementById("infoStatus").textContent = data.project_status || '-';
            infoDiv.style.display = 'block';
        });
    });

    function addRow() {
        const tr = document.createElement("tr");
        tr.className = "item-row";
        tr.innerHTML = `
            <td>
                <select name="products[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${optionsHtml}
                </select>
            </td>
            <td class="stock-text align-middle text-center">-</td>
            <td>
                <input type="number" name="products[${rowIndex}][quantity]" class="form-control qty-input" required min="0.01" step="0.01" value="1">
            </td>
            <td class="unit-text align-middle">-</td>
            <td>
                <input type="number" name="products[${rowIndex}][unit_price]" class="form-control price-input" required min="0" value="0">
            </td>
            <td class="text-end align-middle fw-bold">
                <span class="subtotal-display">0,00</span>
                <input type="hidden" name="products[${rowIndex}][subtotal]" class="subtotal-input" value="0">
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        $(tr).find('.product-select').select2({ width: '100%' });
        rowIndex++;
    }

    function addServiceRow() {
        const tr = document.createElement("tr");
        tr.className = "service-row";
        tr.innerHTML = `
            <td>
                <input type="text" name="services[${serviceIndex}][service_name]" class="form-control" required placeholder="Nama Jasa">
            </td>
            <td>
                <input type="number" name="services[${serviceIndex}][quantity]" class="form-control service-qty-input" required min="0.01" step="0.01" value="1">
            </td>
            <td>
                <input type="number" name="services[${serviceIndex}][unit_price]" class="form-control service-price-input" required min="0" value="0">
            </td>
            <td class="text-end align-middle fw-bold">
                <span class="service-subtotal-display">0,00</span>
                <input type="hidden" name="services[${serviceIndex}][subtotal]" class="service-subtotal-input" value="0">
            </td>
            <td>
                <input type="text" name="services[${serviceIndex}][notes]" class="form-control" placeholder="Keterangan">
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-service"><i class="bi bi-trash"></i></button></td>
        `;
        servicesTbody.appendChild(tr);
        serviceIndex++;
    }

    addServiceRow();
    btnAdd.addEventListener("click", addRow);
    btnAddService.addEventListener("click", addServiceRow);

    tbody.addEventListener("click", function(e) {
        if(e.target.closest(".btn-remove")) {
            const row = e.target.closest("tr");
            const select = $(row).find('.product-select');
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2('destroy');
            }
            row.remove();
            calculateTotal();
        }
    });

    servicesTbody.addEventListener("click", function(e) {
        if(e.target.closest(".btn-remove-service")) {
            e.target.closest("tr").remove();
            calculateTotal();
        }
    });

    $(tbody).on("change", ".product-select", function(e) {
        const row = e.target.closest("tr");
        const pid = e.target.value;
        if(!pid) return;

        fetch(`/sales/orders/api/product-info/${pid}`)
        .then(res => res.json())
        .then(data => {
            row.querySelector(".unit-text").textContent = data.unit_name;
            row.querySelector(".stock-text").textContent = data.available_stock;
        });
    });

    tbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("qty-input") || e.target.classList.contains("price-input")) {
            const row = e.target.closest("tr");
            const qty = parseFloat(row.querySelector(".qty-input").value) || 0;
            const price = parseFloat(row.querySelector(".price-input").value) || 0;
            const sub = qty * price;
            
            row.querySelector(".subtotal-input").value = sub;
            row.querySelector(".subtotal-display").textContent = formatRupiah(sub);
            calculateTotal();
        }
    });

    servicesTbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("service-qty-input") || e.target.classList.contains("service-price-input")) {
            const row = e.target.closest("tr");
            const qty = parseFloat(row.querySelector(".service-qty-input").value) || 0;
            const price = parseFloat(row.querySelector(".service-price-input").value) || 0;
            const sub = qty * price;
            
            row.querySelector(".service-subtotal-input").value = sub;
            row.querySelector(".service-subtotal-display").textContent = formatRupiah(sub);
            calculateTotal();
        }
    });

    function calculateTotal() {
        let totProduk = 0;
        let totJasa = 0;
        document.querySelectorAll(".subtotal-input").forEach(i => totProduk += parseFloat(i.value) || 0);
        document.querySelectorAll(".service-subtotal-input").forEach(i => totJasa += parseFloat(i.value) || 0);
        
        let grandTotal = totProduk + totJasa;
        
        document.getElementById("totalProdukDisplay").textContent = formatRupiah(totProduk);
        document.getElementById("totalJasaDisplay").textContent = formatRupiah(totJasa);
        document.getElementById("totalDisplay").textContent = formatRupiah(grandTotal);
        document.getElementById("totalInput").value = grandTotal;
    }
});
</script>
@endpush
