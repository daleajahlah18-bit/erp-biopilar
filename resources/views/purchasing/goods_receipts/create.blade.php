@extends('layouts.app')
@section('title', 'Terima Barang')
@section('page_title', 'Terima Barang (Goods Receipt)')

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
        <form action="{{ route('purchasing.goods-receipts.store') }}" method="POST" id="grForm">
            @csrf
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header Goods Receipt</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor GR</label>
                    <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Terima</label>
                    <input type="date" name="receipt_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor PR</label>
                    <select name="purchase_order_id" id="poSelect" class="form-select" required>
                        <option value="">-- Pilih PR --</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->po_number }} - {{ $order->supplier->supplier_name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Penerima Barang</label>
                    <input type="text" name="received_by" class="form-control" required placeholder="Nama penerima">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gudang Tujuan</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary mb-3" style="font-weight: 600;">Daftar Barang Diterima</h5>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="">
                        <tr>
                            <th>Product</th>
                            <th width="15%">Unit</th>
                            <th width="15%" class="text-end">Qty PR</th>
                            <th width="15%">Qty Diterima</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Silakan pilih Nomor PR terlebih dahulu</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('purchasing.goods-receipts.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan" disabled>Simpan GR</button>
            </div>
        </form>
    </div>
</div>

@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const poSelect = document.getElementById('poSelect');
    const tbody = document.getElementById('itemsTbody');
    const btnSimpan = document.getElementById('btnSimpan');
    const grForm = document.getElementById('grForm');

    poSelect.addEventListener('change', function() {
        const poId = this.value;
        if (!poId) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Silakan pilih Nomor PR terlebih dahulu</td></tr>';
            btnSimpan.disabled = true;
            return;
        }

        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div> Mengambil data PO...</td></tr>';
        
        fetch(`/purchasing/goods-receipts/api/PR-details/${poId}`)
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = '';
                
                if (data.details.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">PO tidak memiliki item.</td></tr>';
                    btnSimpan.disabled = true;
                    return;
                }

                data.details.forEach((item, index) => {
                    const productName = item.product ? item.product.product_name : '-';
                    const unitName = item.unit ? item.unit.unit_name : '-';
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            ${productName}
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        </td>
                        <td>${unitName}</td>
                        <td class="text-end">
                            ${item.quantity}
                            <input type="hidden" name="items[${index}][qty_po]" value="${item.quantity}" class="input-qty-po">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][qty_received]" class="form-control input-qty-received" min="0" max="${item.quantity}" step="0.01" value="${item.quantity}" required>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                btnSimpan.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching PO:', error);
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Gagal mengambil data PO. Pastikan koneksi server baik.</td></tr>';
                btnSimpan.disabled = true;
            });
    });

    // Validasi form
    grForm.addEventListener('submit', function(e) {
        const receivedInputs = tbody.querySelectorAll('.input-qty-received');
        let totalReceived = 0;
        let valid = true;

        receivedInputs.forEach(input => {
            const val = parseFloat(input.value) || 0;
            const max = parseFloat(input.getAttribute('max')) || 0;
            
            if (val > max) {
                notifyError('Pemberitahuan', 'Qty Diterima tidak boleh melebihi Qty PO!');
                input.focus();
                valid = false;
            }
            totalReceived += val;
        });

        if (totalReceived <= 0 && valid) {
            notifyError('Pemberitahuan', 'Minimal ada satu barang yang diterima!');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>
