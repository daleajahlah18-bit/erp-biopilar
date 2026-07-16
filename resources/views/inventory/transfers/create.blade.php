@extends('layouts.app')
@section('title', 'Transfer Stok')
@section('page_title', 'Transfer Stok')

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

        <form action="{{ route('inventory.transfers.store') }}" method="POST" id="transferForm">
            @csrf
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header Transfer</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">No Transfer</label>
                    <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Transfer</label>
                    <input type="date" name="transfer_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Keterangan opsional">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Asal</label>
                    <select name="source_warehouse_id" id="sourceWarehouse" class="form-select" required>
                        <option value="">-- Pilih Gudang Asal --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Tujuan</label>
                    <select name="destination_warehouse_id" id="destWarehouse" class="form-select" required>
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary mb-0" style="font-weight: 600;">Transfer Items</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddItem" disabled>
                    <i class="bi bi-plus-lg"></i> Tambah Item
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="">
                        <tr>
                            <th width="35%">Product</th>
                            <th width="15%">Unit</th>
                            <th width="20%" class="text-end">Stock Tersedia</th>
                            <th width="20%">Qty Transfer</th>
                            <th width="10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <tr id="emptyRow">
                            <td colspan="5" class="text-center text-secondary py-4">Pilih Gudang Asal terlebih dahulu</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('inventory.transfers.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan">Simpan Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sourceWarehouse = document.getElementById('sourceWarehouse');
    const destWarehouse = document.getElementById('destWarehouse');
    const btnAddItem = document.getElementById('btnAddItem');
    const tbody = document.getElementById('itemsTbody');
    const transferForm = document.getElementById('transferForm');
    
    let productOptions = '';
    let itemIndex = 0;

    // Load produk ketika Gudang Asal berubah
    sourceWarehouse.addEventListener('change', function() {
        const whId = this.value;
        tbody.innerHTML = ''; // Clear items
        itemIndex = 0;

        if (!whId) {
            tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-secondary py-4">Pilih Gudang Asal terlebih dahulu</td></tr>';
            btnAddItem.disabled = true;
            return;
        }

        // Fetch stock products for the selected warehouse
        fetch(`/inventory/transfers/api/products/${whId}`)
            .then(res => res.json())
            .then(data => {
                if (data.stocks.length === 0) {
                    tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-danger py-4">Tidak ada produk dengan stok > 0 di Gudang ini.</td></tr>';
                    btnAddItem.disabled = true;
                    return;
                }

                productOptions = '<option value="">-- Pilih Produk --</option>';
                data.stocks.forEach(stock => {
                    const prodName = stock.product ? stock.product.product_name : 'Unknown';
                    productOptions += `<option value="${stock.product_id}">${prodName}</option>`;
                });

                btnAddItem.disabled = false;
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-secondary py-4">Silakan klik Tambah Item</td></tr>';
            })
            .catch(err => {
                console.error(err);
                notifyError('Pemberitahuan', 'Gagal mengambil data produk.');
            });
    });

    // Tambah Item row
    btnAddItem.addEventListener('click', function() {
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                    ${productOptions}
                </select>
            </td>
            <td><input type="text" class="form-control unit-display " readonly></td>
            <td><input type="text" class="form-control text-end stock-display " readonly></td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" min="0.01" step="0.01" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);

        // Initialize Select2 on the new row
        $(tr).find('.product-select').select2({
            placeholder: '-- Pilih Produk --',
            allowClear: true,
            width: '100%'
        });

        $(tr).find('.product-select').on('change', function() {
            const whId = sourceWarehouse.value;
            const prodId = this.value;
            const row = this.closest('tr');
            const unitDisplay = row.querySelector('.unit-display');
            const stockDisplay = row.querySelector('.stock-display');
            const qtyInput = row.querySelector('.qty-input');

            if (!prodId) {
                unitDisplay.value = '';
                stockDisplay.value = '';
                qtyInput.max = '';
                return;
            }

            fetch(`/inventory/transfers/api/stock/${whId}/${prodId}`)
                .then(res => res.json())
                .then(data => {
                    unitDisplay.value = data.unit_name;
                    stockDisplay.value = data.quantity;
                    qtyInput.max = data.quantity;
                });
        });

        itemIndex++;
    });

    // Hapus row & Handle Product Change
    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove')) {
            const tr = e.target.closest('tr');
            if ($(tr).find('.product-select').hasClass("select2-hidden-accessible")) {
                $(tr).find('.product-select').select2('destroy');
            }
            tr.remove();
            if (tbody.children.length === 0) {
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-secondary py-4">Silakan klik Tambah Item</td></tr>';
            }
        }
    });

    // Handled in Select2 initialization
    // tbody.addEventListener('change', function(e) {
    //     if (e.target.classList.contains('product-select')) {
    //     ...
    //     }
    // });

    // Validasi submit
    transferForm.addEventListener('submit', function(e) {
        if (sourceWarehouse.value === destWarehouse.value) {
            e.preventDefault();
            notifyError('Pemberitahuan', 'Gudang Tujuan tidak boleh sama dengan Gudang Asal!');
            return;
        }

        const rows = tbody.querySelectorAll('tr:not(#emptyRow)');
        if (rows.length === 0) {
            e.preventDefault();
            notifyError('Pemberitahuan', 'Minimal harus ada satu item transfer!');
            return;
        }

        let valid = true;
        rows.forEach(row => {
            const qtyInput = row.querySelector('.qty-input');
            const stockDisplay = row.querySelector('.stock-display');
            
            const qty = parseFloat(qtyInput.value) || 0;
            const maxStock = parseFloat(stockDisplay.value) || 0;

            if (qty > maxStock) {
                notifyError('Pemberitahuan', 'Qty Transfer tidak boleh melebihi Stock Tersedia!');
                qtyInput.focus();
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>
