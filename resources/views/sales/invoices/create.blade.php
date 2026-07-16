
@extends("layouts.app")
@section("title", "Buat Sales Invoice")
@section("page_title", "Sales Invoice")
@section("page_subtitle", "Buat Invoice Baru")

@section("content")
<div class="card">
    <div class="card-body">
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        <form action="{{ route("sales.invoices.store") }}" method="POST" id="invForm">
            @csrf
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>No Invoice</label>
                    <input type="text" class="form-control " readonly placeholder="Auto Generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tanggal Invoice *</label>
                    <input type="date" name="invoice_date" class="form-control" required value="{{ date("Y-m-d") }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Sales Order *</label>
                    <select name="sales_order_id" id="sales_order_id" class="form-select" required>
                        <option value="">-- Pilih SO --</option>
                        @foreach($orders as $o)
                            <option value="{{ $o->id }}">{{ $o->sales_order_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Terms (Dari Master Proyek) *</label>
                    <select name="terms_of_payment_days" id="terms_of_payment_days" class="form-select" required>
                        <option value="0">Pilih Sales Order Dahulu</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Customer</label>
                    <input type="text" id="customer_name" class="form-control " readonly>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Keterangan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>

            <hr>

            <h5 class="text-primary mb-3">Detail Produk</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="">
                        <tr>
                            <th width="30%">Produk</th>
                            <th width="15%">Qty</th>
                            <th width="10%">Unit</th>
                            <th width="15%">Harga (Rp)</th>
                            <th width="20%">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <tr><td colspan="5" class="text-center text-muted">Silakan pilih Sales Order terlebih dahulu.</td></tr>
                    </tbody>
                </table>
            </div>

            <h5 class="text-primary mt-4 mb-3">Detail Jasa</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="">
                        <tr>
                            <th width="30%">Nama Jasa</th>
                            <th width="15%">Qty</th>
                            <th width="20%">Harga (Rp)</th>
                            <th width="20%">Subtotal (Rp)</th>
                            <th width="15%">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="servicesTbody">
                        <tr><td colspan="5" class="text-center text-muted">Silakan pilih Sales Order terlebih dahulu.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
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
                <button type="submit" class="btn-primary-custom px-4">Simpan sebagai Draft</button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const soSelect = document.getElementById("sales_order_id");
    const tbody = document.getElementById("itemsTbody");
    const servicesTbody = document.getElementById("servicesTbody");

    soSelect.addEventListener("change", function() {
        const soId = this.value;
        if (!soId) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Silakan pilih Sales Order terlebih dahulu.</td></tr>`;
            servicesTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Silakan pilih Sales Order terlebih dahulu.</td></tr>`;
            document.getElementById("customer_name").value = "";
            document.getElementById("terms_of_payment_days").innerHTML = '<option value="0">Pilih Sales Order Dahulu</option>';
            return;
        }

        fetch(`/sales/invoices/api/order-details/${soId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("customer_name").value = data.customer.customer_name;
            
            const termsSelect = document.getElementById("terms_of_payment_days");
            termsSelect.innerHTML = "";
            if (data.customer.payment_terms_list && data.customer.payment_terms_list.length > 0) {
                data.customer.payment_terms_list.forEach(term => {
                    const opt = document.createElement("option");
                    opt.value = term.days;
                    opt.textContent = term.name;
                    termsSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement("option");
                opt.value = "0";
                opt.textContent = "Tidak Ada TOP (0 Hari)";
                termsSelect.appendChild(opt);
            }
            
            tbody.innerHTML = "";
            data.details.forEach((item, index) => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>
                        <input type="hidden" name="products[${index}][product_id]" value="${item.product_id}">
                        ${item.product_name}
                    </td>
                    <td>
                        <input type="number" name="products[${index}][quantity]" class="form-control qty-input" required min="0.01" step="any" value="${item.quantity}" readonly>
                    </td>
                    <td class="align-middle">${item.unit_name}</td>
                    <td>
                        <input type="number" name="products[${index}][unit_price]" class="form-control price-input" required min="0" value="${item.unit_price}">
                        <small class="text-muted">Bisa diedit</small>
                    </td>
                    <td class="text-end align-middle fw-bold">
                        <span class="subtotal-display">${parseFloat(item.quantity * item.unit_price).toLocaleString("id-ID", {minimumFractionDigits:2})}</span>
                        <input type="hidden" name="products[${index}][subtotal]" class="subtotal-input" value="${item.quantity * item.unit_price}">
                    </td>
                `;
                tbody.appendChild(tr);
            });

            servicesTbody.innerHTML = "";
            if (data.services && data.services.length > 0) {
                data.services.forEach((item, index) => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>
                            <input type="hidden" name="services[${index}][service_name]" value="${item.service_name}">
                            ${item.service_name}
                        </td>
                        <td>
                            <input type="number" name="services[${index}][quantity]" class="form-control service-qty-input" required min="0.01" step="0.01" value="${item.quantity}" readonly>
                        </td>
                        <td>
                            <input type="number" name="services[${index}][unit_price]" class="form-control service-price-input" required min="0" value="${item.unit_price}">
                            <small class="text-muted">Bisa diedit</small>
                        </td>
                        <td class="text-end align-middle fw-bold">
                            <span class="service-subtotal-display">${parseFloat(item.quantity * item.unit_price).toLocaleString("id-ID", {minimumFractionDigits:2})}</span>
                            <input type="hidden" name="services[${index}][subtotal]" class="service-subtotal-input" value="${item.quantity * item.unit_price}">
                        </td>
                        <td>
                            <input type="hidden" name="services[${index}][notes]" value="${item.notes || ''}">
                            ${item.notes || '-'}
                        </td>
                    `;
                    servicesTbody.appendChild(tr);
                });
            } else {
                servicesTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Tidak ada jasa pada pesanan ini.</td></tr>`;
            }

            calculateTotal();
        });
    });

    tbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("price-input")) {
            const row = e.target.closest("tr");
            const qty = parseFloat(row.querySelector(".qty-input").value) || 0;
            const price = parseFloat(row.querySelector(".price-input").value) || 0;
            const sub = qty * price;
            
            row.querySelector(".subtotal-input").value = sub;
            row.querySelector(".subtotal-display").textContent = sub.toLocaleString("id-ID", {minimumFractionDigits:2});
            calculateTotal();
        }
    });

    servicesTbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("service-price-input")) {
            const row = e.target.closest("tr");
            const qty = parseFloat(row.querySelector(".service-qty-input").value) || 0;
            const price = parseFloat(row.querySelector(".service-price-input").value) || 0;
            const sub = qty * price;
            
            row.querySelector(".service-subtotal-input").value = sub;
            row.querySelector(".service-subtotal-display").textContent = sub.toLocaleString("id-ID", {minimumFractionDigits:2});
            calculateTotal();
        }
    });

    function calculateTotal() {
        let totProduk = 0;
        let totJasa = 0;
        document.querySelectorAll(".subtotal-input").forEach(i => totProduk += parseFloat(i.value) || 0);
        document.querySelectorAll(".service-subtotal-input").forEach(i => totJasa += parseFloat(i.value) || 0);
        
        let grandTotal = totProduk + totJasa;
        
        document.getElementById("totalProdukDisplay").textContent = totProduk.toLocaleString("id-ID", {minimumFractionDigits:2});
        document.getElementById("totalJasaDisplay").textContent = totJasa.toLocaleString("id-ID", {minimumFractionDigits:2});
        document.getElementById("totalDisplay").textContent = grandTotal.toLocaleString("id-ID", {minimumFractionDigits:2});
        document.getElementById("totalInput").value = grandTotal;
    }
});
</script>
