@extends('layouts.app')
@section('title', 'Buat Project Fabrication')
@section('page_title', 'Project Fabrication')
@section('page_subtitle', 'Catat Pemakaian Barang')

@section('content')

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('production.project-productions.store') }}" method="POST" id="ppForm">
            @csrf
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3 text-primary" style="font-weight: 600;">Header Dokumen</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">No Dokumen</label>
                            <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="production_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                <option value="">-- Pilih Project --</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Gudang Asal <span class="text-danger">*</span></label>
                            <select name="warehouse_id" id="warehouseSelect" class="form-select" required>
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Catatan / Keterangan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Tujuan pemakaian...">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-primary m-0" style="font-weight: 600;">Daftar Pemakaian Barang</h5>
                        <button type="button" class="btn btn-sm btn-success" id="btnAddItem">
                            <i class="bi bi-plus-circle"></i> Tambah Barang
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="35%">Product / Barang</th>
                                    <th width="15%">Unit</th>
                                    <th width="20%">Stock Tersedia</th>
                                    <th width="20%">Qty Dipakai</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTbody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-primary m-0" style="font-weight: 600;">Jasa / Biaya Tambahan</h5>
                        <button type="button" class="btn btn-sm btn-success" id="btnAddService">
                            <i class="bi bi-plus-circle"></i> Tambah Jasa
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="35%">Nama Jasa</th>
                                    <th width="15%">Qty</th>
                                    <th width="20%">Harga Satuan</th>
                                    <th width="20%">Subtotal</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="servicesTbody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('production.project-productions.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                        <button type="submit" class="btn-primary-custom"><i class="bi bi-save"></i> Simpan Project Fabrication</button>
                    </div>
                </div>
            </div>
        </form>

<div id="productOptions" style="display:none;">
    <option value="">-- Pilih Barang --</option>
    @foreach($products as $prod)
        <option value="{{ $prod->id }}">{{ $prod->product_name }} ({{ $prod->product_code }})</option>
    @endforeach
</div>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('itemsTbody');
    const btnAdd = document.getElementById('btnAddItem');
    const productOptionsHtml = document.getElementById('productOptions').innerHTML;
    const warehouseSelect = document.getElementById('warehouseSelect');
    let rowIndex = 0;

    function addRow() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${productOptionsHtml}
                </select>
                <div class="warning-text text-danger mt-1" style="font-size:0.8rem; display:none;">
                    Stok tidak mencukupi.
                </div>
                <div class="hpp-info text-muted mt-1" style="font-size:0.8rem; display:none;">
                    HPP: <span class="hpp-value">Rp0</span>
                </div>
            </td>
            <td class="unit-text align-middle">-</td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text stock-display ">0</span>
                    <input type="hidden" class="stock-input" value="0">
                </div>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm qty-input" min="0.01" step="0.01" required value="1">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);

        // Initialize Select2 on the new row
        $(tr).find('.product-select').select2({
            placeholder: '-- Pilih Barang --',
            allowClear: true,
            width: '100%'
        });

        $(tr).find('.product-select').on('change', function() {
            checkStock(this);
        });

        rowIndex++;
    }

    addRow();
    btnAdd.addEventListener('click', addRow);

    const btnAddService = document.getElementById('btnAddService');
    const servicesTbody = document.getElementById('servicesTbody');
    let serviceIndex = 0;

    function addServiceRow() {
        const tr = document.createElement('tr');
        tr.className = 'service-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="services[${serviceIndex}][service_name]" class="form-control" required placeholder="Nama Jasa">
            </td>
            <td>
                <input type="number" name="services[${serviceIndex}][quantity]" class="form-control service-qty" min="0.01" step="0.01" required value="1">
            </td>
            <td>
                <input type="number" name="services[${serviceIndex}][unit_price]" class="form-control service-price" min="0" step="1" required value="0">
            </td>
            <td>
                <input type="text" class="form-control service-subtotal " readonly value="0">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-service"><i class="bi bi-trash"></i></button>
            </td>
        `;
        servicesTbody.appendChild(tr);
        serviceIndex++;
    }

    btnAddService.addEventListener('click', addServiceRow);

    servicesTbody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-service')) {
            e.target.closest('tr').remove();
        }
    });

    servicesTbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('service-qty') || e.target.classList.contains('service-price')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.service-qty').value) || 0;
            const price = parseFloat(row.querySelector('.service-price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.service-subtotal').value = new Intl.NumberFormat('id-ID').format(subtotal);
        }
    });

    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove')) {
            const rowCount = document.querySelectorAll('.item-row').length;
            if (rowCount > 1) {
                const tr = e.target.closest('tr');
                if ($(tr).find('.product-select').hasClass("select2-hidden-accessible")) {
                    $(tr).find('.product-select').select2('destroy');
                }
                tr.remove();
            } else {
                notifyError('Pemberitahuan', 'Minimal harus ada 1 barang.');
            }
        }
    });

    warehouseSelect.addEventListener('change', function() {
        document.querySelectorAll('.product-select').forEach(select => {
            if (select.value) {
                checkStock(select);
            }
        });
    });

    tbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input')) {
            validateQty(e.target.closest('tr'));
        }
    });

    document.getElementById('ppForm').addEventListener('submit', function(e) {
        let isValid = true;
        document.querySelectorAll('.item-row').forEach(row => {
            if (!validateQty(row)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            notifyError('Pemberitahuan', 'Terdapat Qty Dipakai yang melebihi Stok Tersedia. Silakan periksa kembali.');
        }
    });

    function checkStock(selectElement) {
        const row = selectElement.closest('tr');
        const productId = selectElement.value;
        const warehouseId = warehouseSelect.value;
        
        if (!productId) {
            resetRow(row);
            return;
        }

        const allSelects = document.querySelectorAll('.product-select');
        let isDuplicate = false;
        allSelects.forEach(sel => {
            if (sel !== selectElement && sel.value === productId) {
                isDuplicate = true;
            }
        });

        if (isDuplicate) {
            notifyError('Pemberitahuan', 'Barang ini sudah ditambahkan.');
            $(selectElement).val(null).trigger('change.select2');
            resetRow(row);
            return;
        }

        if (!warehouseId) {
            notifyError('Pemberitahuan', 'Pilih Gudang Asal terlebih dahulu.');
            $(selectElement).val(null).trigger('change.select2');
            resetRow(row);
            return;
        }

        fetch(`/production/project-productions/api/stock/${warehouseId}/${productId}`)
            .then(res => res.json())
            .then(data => {
                row.querySelector('.unit-text').textContent = data.unit_name;
                row.querySelector('.stock-input').value = data.stock_available;
                row.querySelector('.stock-display').textContent = parseFloat(data.stock_available).toLocaleString('id-ID', {minimumFractionDigits: 0});
                
                // Tampilkan HPP jika ada
                if (data.hpp !== undefined) {
                    row.querySelector('.hpp-info').style.display = 'block';
                    row.querySelector('.hpp-value').textContent = 'Rp' + parseFloat(data.hpp).toLocaleString('id-ID');
                } else {
                    row.querySelector('.hpp-info').style.display = 'none';
                }

                validateQty(row);
            })
            .catch(err => {
                console.error(err);
                row.querySelector('.unit-text').textContent = '-';
                row.querySelector('.stock-input').value = 0;
                row.querySelector('.stock-display').textContent = '0';
                row.querySelector('.hpp-info').style.display = 'none';
            });
    }

    function resetRow(row) {
        row.querySelector('.unit-text').textContent = '-';
        row.querySelector('.stock-input').value = 0;
        row.querySelector('.stock-display').textContent = '0';
        row.querySelector('.qty-input').value = 1;
        row.querySelector('.warning-text').style.display = 'none';
        row.querySelector('.hpp-info').style.display = 'none';
    }

    function validateQty(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const stock = parseFloat(row.querySelector('.stock-input').value) || 0;
        const warning = row.querySelector('.warning-text');
        
        if (qty > stock) {
            warning.style.display = 'block';
            row.querySelector('.qty-input').classList.add('is-invalid');
            return false;
        } else {
            warning.style.display = 'none';
            row.querySelector('.qty-input').classList.remove('is-invalid');
            return true;
        }
    }
});
</script>
