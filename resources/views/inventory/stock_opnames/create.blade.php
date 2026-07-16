@extends('layouts.app')
@section('title', 'Buat Stock Opname')
@section('page_title', 'Stock Opname')
@section('page_subtitle', 'Penyesuaian Fisik Stok')

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

        <form action="{{ route('inventory.stock-opnames.store') }}" method="POST" id="opnameForm">
            @csrf
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header Opname</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">No Opname</label>
                    <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Opname</label>
                    <input type="date" name="opname_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang</label>
                    <select name="warehouse_id" id="warehouseId" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan / Keterangan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Opsional">
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary mb-3" style="font-weight: 600;">Detail Perhitungan Fisik</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-custom">
                    <thead class="">
                        <tr>
                            <th width="35%">Product</th>
                            <th width="15%">Unit</th>
                            <th width="15%" class="text-end">Stock Sistem</th>
                            <th width="15%">Stock Fisik</th>
                            <th width="20%" class="text-end">Selisih</th>
                        </tr>
                    </thead>
                    <tbody id="productsTbody">
                        <tr id="emptyRow">
                            <td colspan="5" class="text-center text-secondary py-4">Pilih Gudang untuk memuat produk.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('inventory.stock-opnames.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan" disabled>Simpan Stock Opname</button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('warehouseId');
    const tbody = document.getElementById('productsTbody');
    const btnSimpan = document.getElementById('btnSimpan');

    let products = [];

    warehouseSelect.addEventListener('change', function() {
        const whId = this.value;
        if (!whId) {
            tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-secondary py-4">Pilih Gudang untuk memuat produk.</td></tr>';
            btnSimpan.disabled = true;
            return;
        }

        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Loading data stok...</td></tr>';
        
        fetch(`/inventory/stock-opnames/api/products/${whId}`)
            .then(res => res.json())
            .then(data => {
                products = data.products;
                renderTable();
            })
            .catch(err => {
                console.error(err);
                notifyError('Pemberitahuan', 'Gagal mengambil data produk.');
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-danger py-4">Gagal memuat.</td></tr>';
            });
    });

    function renderTable() {
        if (products.length === 0) {
            tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-secondary py-4">Tidak ada produk ditemukan.</td></tr>';
            btnSimpan.disabled = true;
            return;
        }

        tbody.innerHTML = '';
        products.forEach((prod, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="hidden" name="items[${index}][product_id]" value="${prod.product_id}">
                    ${prod.product_name}
                </td>
                <td>${prod.unit_name}</td>
                <td class="text-end">
                    <input type="hidden" name="items[${index}][system_stock]" value="${prod.system_stock}">
                    ${parseFloat(prod.system_stock).toLocaleString('id-ID')}
                </td>
                <td>
                    <input type="number" name="items[${index}][physical_stock]" class="form-control physical-input" min="0" step="0.01" value="${prod.system_stock}" required>
                </td>
                <td class="text-end fw-bold diff-display">
                    0
                </td>
            `;
            tbody.appendChild(tr);
        });

        btnSimpan.disabled = false;
    }

    tbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('physical-input')) {
            const row = e.target.closest('tr');
            const systemStock = parseFloat(row.querySelector('input[name*="[system_stock]"]').value) || 0;
            const physicalStock = parseFloat(e.target.value) || 0;
            const diffDisplay = row.querySelector('.diff-display');
            
            const diff = physicalStock - systemStock;
            
            diffDisplay.textContent = diff > 0 ? '+' + diff.toFixed(4) : diff.toFixed(4);
            
            if (diff > 0) {
                diffDisplay.className = 'text-end fw-bold diff-display text-success';
            } else if (diff < 0) {
                diffDisplay.className = 'text-end fw-bold diff-display text-danger';
            } else {
                diffDisplay.className = 'text-end fw-bold diff-display text-secondary';
            }
        }
    });
});
</script>
