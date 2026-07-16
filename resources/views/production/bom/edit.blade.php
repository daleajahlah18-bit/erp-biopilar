@extends('layouts.app')
@section('title', 'Edit Bill of Material')
@section('page_title', 'Bill of Material')
@section('page_subtitle', 'Edit Resep / Komposisi')

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

        <form action="{{ route('production.bom.update', $bom->id) }}" method="POST" id="bomForm">
            @csrf
            @method('PUT')
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header BOM</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">No BOM</label>
                    <input type="text" class="form-control " value="{{ $bom->bom_number }}" readonly>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nama BOM / Resep <span class="text-danger">*</span></label>
                    <input type="text" name="bom_name" class="form-control" required value="{{ $bom->bom_name }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Produk Jadi (Opsional)</label>
                    <select name="product_id" class="form-select">
                        <option value="">-- Tidak Dipetakan --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ $bom->product_id == $prod->id ? 'selected' : '' }}>{{ $prod->product_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="auto_create_product" id="auto_create_product" value="1" {{ !$bom->product_id ? 'checked' : '' }}>
                        <label class="form-check-label text-success" for="auto_create_product" style="font-size: 0.85rem;">
                            Otomatis buat Produk Baru jika tidak dipilih
                        </label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan / Keterangan</label>
                    <input type="text" name="notes" class="form-control" value="{{ $bom->notes }}">
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary mb-0" style="font-weight: 600;">Daftar Bahan Baku</h5>
                <button type="button" class="btn-primary-custom py-1 px-3" id="btnAddMaterial">
                    <i class="bi bi-plus-lg"></i> Tambah Bahan
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-custom">
                    <thead class="">
                        <tr>
                            <th width="30%">Product / Bahan Baku</th>
                            <th width="15%">Unit</th>
                            <th width="15%">Qty</th>
                            <th width="15%" class="text-end">Harga (Rp)</th>
                            <th width="15%" class="text-end">Subtotal (Rp)</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="materialsTbody">
                        <!-- Existing rows -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total HPP / COGS :</td>
                            <td class="text-end fw-bold fs-5 text-primary" id="totalHppDisplay">{{ number_format($bom->total_hpp, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <input type="hidden" name="total_hpp" id="totalHppInput" value="{{ $bom->total_hpp }}">

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('production.bom.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="productOptions" style="display:none;">
    <option value="">-- Pilih Bahan --</option>
    @foreach($materials as $mat)
        <option value="{{ $mat->id }}">{{ $mat->product_name }} ({{ $mat->product_code }})</option>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('materialsTbody');
    const btnAdd = document.getElementById('btnAddMaterial');
    const productOptionsHtml = document.getElementById('productOptions').innerHTML;
    let rowIndex = 0;

    const existingDetails = @json($bom->details);

    function addRow(detail = null) {
        const tr = document.createElement('tr');
        tr.className = 'material-row';
        
        let selectedProductHtml = '<option value="">-- Pilih Bahan --</option>';
        @foreach($materials as $mat)
            selectedProductHtml += `<option value="{{ $mat->id }}" ${detail && detail.product_id == {{ $mat->id }} ? 'selected' : ''}>{{ $mat->product_name }} ({{ $mat->product_code }})</option>`;
        @endforeach

        const unitName = detail && detail.product && detail.product.unit ? detail.product.unit.unit_name : '-';
        const qty = detail ? detail.quantity : 1;
        const unitCost = detail ? detail.unit_cost : 0;
        const subtotal = detail ? detail.subtotal : 0;

        tr.innerHTML = `
            <td>
                <select name="materials[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${selectedProductHtml}
                </select>
                <div class="warning-text text-danger mt-1" style="font-size:0.8rem; display:none;">
                    Belum memiliki riwayat pembelian.
                </div>
            </td>
            <td class="unit-text align-middle">${unitName}</td>
            <td>
                <input type="number" name="materials[${rowIndex}][quantity]" class="form-control qty-input" min="0.01" step="0.01" required value="${qty}">
            </td>
            <td class="text-end align-middle">
                <span class="price-display">${parseFloat(unitCost).toLocaleString('id-ID', {minimumFractionDigits: 2})}</span>
                <input type="hidden" name="materials[${rowIndex}][unit_cost]" class="price-input" value="${unitCost}">
            </td>
            <td class="text-end align-middle fw-bold text-primary">
                <span class="subtotal-display">${parseFloat(subtotal).toLocaleString('id-ID', {minimumFractionDigits: 2})}</span>
                <input type="hidden" name="materials[${rowIndex}][subtotal]" class="subtotal-input" value="${subtotal}">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        
        // Initialize Select2
        $(tr).find('.product-select').select2({
            placeholder: '-- Pilih Bahan --',
            allowClear: true,
            width: '100%'
        });

        rowIndex++;
        
        if(!detail) {
            calculateTotal();
        }
    }

    if (existingDetails.length > 0) {
        existingDetails.forEach(detail => addRow(detail));
    } else {
        addRow();
    }

    btnAdd.addEventListener('click', () => addRow(null));

    $('#materialsTbody').on('click', '.btn-remove', function(e) {
        const rowCount = document.querySelectorAll('.material-row').length;
        if (rowCount > 1) {
            $(this).closest('tr').find('.product-select').select2('destroy');
            $(this).closest('tr').remove();
            calculateTotal();
        } else {
            notifyError('Pemberitahuan', 'Minimal harus ada 1 bahan baku.');
        }
    });

    $('#materialsTbody').on('change', '.product-select', function(e) {
            const row = e.target.closest('tr');
            const productId = e.target.value;
            
            if (!productId) {
                resetRow(row);
                calculateTotal();
                return;
            }

            const allSelects = document.querySelectorAll('.product-select');
            let isDuplicate = false;
            allSelects.forEach(sel => {
                if (sel !== e.target && sel.value === productId) {
                    isDuplicate = true;
                }
            });

            if (isDuplicate) {
                notifyError('Pemberitahuan', 'Bahan baku ini sudah ditambahkan.');
                e.target.value = '';
                resetRow(row);
                calculateTotal();
                return;
            }

            fetch(`/production/bom/api/product-info/${productId}`)
                .then(res => res.json())
                .then(data => {
                    row.querySelector('.unit-text').textContent = data.unit_name;
                    row.querySelector('.price-input').value = data.unit_price;
                    row.querySelector('.price-display').textContent = parseFloat(data.unit_price).toLocaleString('id-ID', {minimumFractionDigits: 2});
                    
                    const warning = row.querySelector('.warning-text');
                    if (data.has_purchase_history) {
                        warning.style.display = 'none';
                    } else {
                        warning.style.display = 'block';
                    }
                    
                    calculateRowSubtotal(row);
                })
                .catch(err => {
                    console.error(err);
                    notifyError('Pemberitahuan', 'Gagal mengambil data produk.');
                    resetRow(row);
                });
    });

    tbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input')) {
            const row = e.target.closest('tr');
            calculateRowSubtotal(row);
        }
    });

    function resetRow(row) {
        row.querySelector('.unit-text').textContent = '-';
        row.querySelector('.price-input').value = 0;
        row.querySelector('.price-display').textContent = '0,00';
        row.querySelector('.qty-input').value = 1;
        row.querySelector('.warning-text').style.display = 'none';
        calculateRowSubtotal(row);
    }

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        
        row.querySelector('.subtotal-input').value = subtotal;
        row.querySelector('.subtotal-display').textContent = subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2});
        
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        document.getElementById('totalHppInput').value = total;
        document.getElementById('totalHppDisplay').textContent = total.toLocaleString('id-ID', {minimumFractionDigits: 2});
    }
});
</script>
@endpush
