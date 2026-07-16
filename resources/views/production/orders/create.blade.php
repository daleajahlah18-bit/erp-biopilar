@extends('layouts.app')
@section('title', 'Buat Production Order')
@section('page_title', 'Production Order')
@section('page_subtitle', 'Pembuatan Perintah Produksi Baru')

@section('content')
<div class="card mb-4">
    <div class="card-body">
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

        <form action="{{ route('production.orders.store') }}" method="POST" id="productionForm">
            @csrf
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header Produksi</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">No Produksi</label>
                    <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Produksi</label>
                    <input type="date" name="production_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Produksi</label>
                    <select name="warehouse_id" id="warehouseId" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bill of Material</label>
                    <select name="bill_of_material_id" id="bomId" class="form-select" required>
                        <option value="">-- Pilih BOM --</option>
                        @foreach($boms as $bom)
                            <option value="{{ $bom->id }}">{{ $bom->bom_number }} - {{ $bom->product->product_name ?? 'Tanpa Produk Jadi' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Target Quantity</label>
                    <input type="number" name="target_quantity" id="targetQty" class="form-control" min="0.01" step="any" required>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Keterangan / Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary mb-0" style="font-weight: 600;">Kebutuhan Produksi</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddItem">
                    <i class="bi bi-plus-lg"></i> Tambah Item
                </button>
            </div>

            <div id="stockWarning" class="alert alert-warning d-none">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Stock bahan baku tidak mencukupi atau terdapat duplikasi item.</strong>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-custom" id="materialsTable">
                    <thead class="">
                        <tr>
                            <th width="30%">Product / Bahan</th>
                            <th width="15%">Unit</th>
                            <th width="15%" class="text-end">Qty per BOM</th>
                            <th width="15%" class="text-end">Stock Tersedia</th>
                            <th width="15%" class="text-end">Qty Dibutuhkan</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="materialsTbody">
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-secondary py-4">Pilih Gudang, BOM, dan isi Target Quantity untuk memuat bahan baku.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('production.orders.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan" disabled>Simpan Draft</button>
            </div>
        </form>
    </div>
</div>

<!-- Template Row -->
<table style="display:none;">
    <tbody id="rowTemplate">
        <tr class="material-row">
            <td>
                <select class="form-select product-select" required>
                    <option value="">-- Pilih Item --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->product_code }} - {{ $prod->product_name }}</option>
                    @endforeach
                </select>
                <input type="hidden" class="input-product-id" name="materials[INDEX][product_id]">
            </td>
            <td class="unit-display text-center">-</td>
            <td class="text-end">
                <input type="number" class="form-control text-end input-qty-bom" name="materials[INDEX][quantity_per_bom]" value="0" min="0" step="any" required>
            </td>
            <td class="text-end fw-bold stock-display">-</td>
            <td class="text-end">
                <input type="number" class="form-control text-end input-qty-required" name="materials[INDEX][quantity_required]" value="0" min="0.01" step="any" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </tbody>
</table>

@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('warehouseId');
    const bomSelect = document.getElementById('bomId');
    const targetQtyInput = document.getElementById('targetQty');
    const tbody = document.getElementById('materialsTbody');
    const btnAddItem = document.getElementById('btnAddItem');
    const stockWarning = document.getElementById('stockWarning');
    const btnSimpan = document.getElementById('btnSimpan');
    const form = document.getElementById('productionForm');
    const rowTemplate = document.getElementById('rowTemplate').innerHTML;

    let rowIndex = 0;

    bomSelect.addEventListener('change', fetchBomMaterials);
    warehouseSelect.addEventListener('change', function() {
        // Jika gudang diganti, re-fetch stock untuk semua row yang ada
        recheckAllStock();
    });
    
    targetQtyInput.addEventListener('input', function() {
        // Otomatis kalikan qty_bom * target untuk setiap row
        const target = parseFloat(this.value) || 0;
        const rows = tbody.querySelectorAll('.material-row');
        rows.forEach(row => {
            const qtyBomInput = row.querySelector('.input-qty-bom');
            const qtyReqInput = row.querySelector('.input-qty-required');
            const qtyBom = parseFloat(qtyBomInput.value) || 0;
            if(qtyBom > 0 && target > 0) {
                qtyReqInput.value = parseFloat((qtyBom * target).toFixed(4));
            }
        });
        validateForm();
    });

    btnAddItem.addEventListener('click', function() {
        removeEmptyRow();
        const html = rowTemplate.replace(/INDEX/g, rowIndex++);
        tbody.insertAdjacentHTML('beforeend', html);
        const newRow = tbody.lastElementChild;
        attachRowEvents(newRow);
        validateForm();
    });

    function removeEmptyRow() {
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();
    }

    function fetchBomMaterials() {
        const bomId = bomSelect.value;
        const whId = warehouseSelect.value;

        if (!bomId) {
            tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-secondary py-4">Pilih BOM terlebih dahulu.</td></tr>';
            validateForm();
            return;
        }

        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Loading bahan baku...</td></tr>';
        
        fetch(`/production/orders/api/bom/${bomId}`)
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    notifyError('Terjadi Kesalahan', data.error);
                    return;
                }
                
                tbody.innerHTML = '';
                if(data.materials.length === 0) {
                    tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-secondary py-4">BOM ini tidak memiliki item. Silakan tambah item secara manual.</td></tr>';
                    validateForm();
                    return;
                }

                data.materials.forEach(mat => {
                    const html = rowTemplate.replace(/INDEX/g, rowIndex++);
                    tbody.insertAdjacentHTML('beforeend', html);
                    const row = tbody.lastElementChild;
                    
                    const select = row.querySelector('.product-select');
                    const hiddenId = row.querySelector('.input-product-id');
                    const qtyBomInput = row.querySelector('.input-qty-bom');
                    
                    select.value = mat.product_id;
                    hiddenId.value = mat.product_id;
                    qtyBomInput.value = parseFloat(mat.quantity_per_bom);
                    
                    row.querySelector('.unit-display').textContent = mat.unit_name;
                    
                    attachRowEvents(row);
                    fetchRowStock(row, mat.product_id);
                });

                // Trigger target qty recalculation
                targetQtyInput.dispatchEvent(new Event('input'));
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-danger py-4">Gagal memuat BOM.</td></tr>';
            });
    }

    function attachRowEvents(row) {
        const select = row.querySelector('.product-select');
        const hiddenId = row.querySelector('.input-product-id');
        const qtyReqInput = row.querySelector('.input-qty-required');
        const btnRemove = row.querySelector('.btn-remove');
        const qtyBomInput = row.querySelector('.input-qty-bom');

        $(select).select2({
            placeholder: '-- Pilih Item --',
            allowClear: true,
            width: '100%'
        });

        $(select).on('change', function() {
            const prodId = this.value;
            hiddenId.value = prodId;
            if (prodId) {
                fetchRowStock(row, prodId);
            } else {
                row.querySelector('.unit-display').textContent = '-';
                row.querySelector('.stock-display').textContent = '-';
                row.setAttribute('data-stock', '0');
                validateForm();
            }
        });

        qtyBomInput.addEventListener('input', function() {
            const target = parseFloat(targetQtyInput.value) || 0;
            const qtyBom = parseFloat(this.value) || 0;
            if(qtyBom > 0 && target > 0) {
                qtyReqInput.value = parseFloat((qtyBom * target).toFixed(4));
            }
            validateForm();
        });

        qtyReqInput.addEventListener('input', validateForm);

        btnRemove.addEventListener('click', function() {
            if ($(select).hasClass("select2-hidden-accessible")) {
                $(select).select2('destroy');
            }
            row.remove();
            if (tbody.children.length === 0) {
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-secondary py-4">Pilih BOM atau tambah item secara manual.</td></tr>';
            }
            validateForm();
        });
    }

    function fetchRowStock(row, prodId) {
        const whId = warehouseSelect.value;
        const stockDisplay = row.querySelector('.stock-display');
        const unitDisplay = row.querySelector('.unit-display');
        
        if (!whId) {
            stockDisplay.textContent = 'Pilih Gudang';
            row.setAttribute('data-stock', '0');
            validateForm();
            return;
        }

        stockDisplay.innerHTML = '<span class="spinner-border spinner-border-sm text-secondary"></span>';
        
        fetch(`/production/orders/api/stock/${whId}/${prodId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    stockDisplay.textContent = '0';
                    row.setAttribute('data-stock', '0');
                } else {
                    stockDisplay.textContent = parseFloat(data.stock_available).toLocaleString('id-ID');
                    unitDisplay.textContent = data.unit_name;
                    row.setAttribute('data-stock', data.stock_available);
                }
                validateForm();
            })
            .catch(err => {
                console.error(err);
                stockDisplay.textContent = 'Error';
                row.setAttribute('data-stock', '0');
                validateForm();
            });
    }

    function recheckAllStock() {
        const rows = tbody.querySelectorAll('.material-row');
        rows.forEach(row => {
            const prodId = row.querySelector('.input-product-id').value;
            if (prodId) {
                fetchRowStock(row, prodId);
            }
        });
    }

    function validateForm() {
        const rows = tbody.querySelectorAll('.material-row');
        let isValid = true;
        let hasInsufficientStock = false;
        let hasDuplicate = false;
        
        const target = parseFloat(targetQtyInput.value) || 0;
        const whId = warehouseSelect.value;
        const bomId = bomSelect.value;

        if (!whId || !bomId || target <= 0 || rows.length === 0) {
            isValid = false;
        }

        let products = new Set();

        rows.forEach(row => {
            const prodId = row.querySelector('.input-product-id').value;
            const required = parseFloat(row.querySelector('.input-qty-required').value) || 0;
            const stock = parseFloat(row.getAttribute('data-stock')) || 0;
            const reqInput = row.querySelector('.input-qty-required');

            if (!prodId || required <= 0) {
                isValid = false;
            }

            if (products.has(prodId)) {
                hasDuplicate = true;
                isValid = false;
            }
            if(prodId) {
                products.add(prodId);
            }

            if (required > stock) {
                hasInsufficientStock = true;
                isValid = false;
                reqInput.classList.add('is-invalid');
                reqInput.classList.remove('is-valid');
            } else if (required > 0 && stock >= required) {
                reqInput.classList.remove('is-invalid');
                reqInput.classList.add('is-valid');
            }
        });

        if (hasInsufficientStock || hasDuplicate) {
            stockWarning.classList.remove('d-none');
            stockWarning.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>' + 
                (hasDuplicate ? 'Terdapat duplikasi product.' : 'Stock bahan baku tidak mencukupi.') + 
                '</strong>';
        } else {
            stockWarning.classList.add('d-none');
        }

        btnSimpan.disabled = !isValid;
    }

    form.addEventListener('submit', function(e) {
        if (btnSimpan.disabled) e.preventDefault();
    });
});
</script>