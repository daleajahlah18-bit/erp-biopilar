@extends('layouts.app')
@section('title', 'Buat Bill of Material')
@section('page_title', 'Bill of Material')
@section('page_subtitle', 'Resep / Komposisi Produk')

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

        <form action="{{ route('production.bom.store') }}" method="POST" id="bomForm">
            @csrf
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header BOM</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">No BOM</label>
                    <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nama BOM / Resep <span class="text-danger">*</span></label>
                    <input type="text" name="bom_name" class="form-control" required placeholder="Contoh: Ayam Bakar Spesial">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Produk Jadi (Opsional)</label>
                    <select name="product_id" class="form-select">
                        <option value="">-- Tidak Dipetakan --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->product_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="auto_create_product" id="auto_create_product" value="1" checked>
                        <label class="form-check-label text-success" for="auto_create_product" style="font-size: 0.85rem;">
                            Otomatis buat Produk Baru jika tidak dipilih
                        </label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan / Keterangan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Instruksi pembuatan...">
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
                        <!-- Rows dinamis -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total HPP / COGS :</td>
                            <td class="text-end fw-bold fs-5 text-primary" id="totalHppDisplay">0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <input type="hidden" name="total_hpp" id="totalHppInput" value="0">

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('production.bom.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan">Simpan BOM</button>
            </div>
        </form>
    </div>
</div>

<!-- Template untuk select product (hidden) -->
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

    function addRow() {
        const tr = document.createElement('tr');
        tr.className = 'material-row';
        tr.innerHTML = `
            <td>
                <select name="materials[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${productOptionsHtml}
                </select>
                <div class="warning-text text-danger mt-1" style="font-size:0.8rem; display:none;">
                    Belum memiliki riwayat pembelian.
                </div>
            </td>
            <td class="unit-text align-middle">-</td>
            <td>
                <input type="number" name="materials[${rowIndex}][quantity]" class="form-control qty-input" min="0.01" step="0.01" required value="1">
            </td>
            <td class="text-end align-middle">
                <span class="price-display">0,00</span>
                <input type="hidden" name="materials[${rowIndex}][unit_cost]" class="price-input" value="0">
            </td>
            <td class="text-end align-middle fw-bold text-primary">
                <span class="subtotal-display">0,00</span>
                <input type="hidden" name="materials[${rowIndex}][subtotal]" class="subtotal-input" value="0">
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
        calculateTotal();
    }

    // Initialize one row
    addRow();

    btnAdd.addEventListener('click', addRow);

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

            // Validasi duplikat
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

            // AJAX call to get product info and latest price
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
