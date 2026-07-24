@extends('layouts.app')
@section('title', 'Buat Purchase Release')
@section('page_title', 'Buat Purchase Release')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('purchasing.purchase-orders.store') }}" method="POST" id="poForm">
            @csrf
            
            <h5 class="mb-3 text-primary" style="font-weight: 600;">Header Purchase Release</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor PR</label>
                    <input type="text" class="form-control " placeholder="Auto Generate" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal PR</label>
                    <input type="date" name="po_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Project (Opsional)</label>
                    <select name="project_id" class="form-select">
                        <option value="">-- Bukan Pembelian Project --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_name }} ({{ $project->client_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-10 mb-3">
                    <label class="form-label">Keterangan Project</label>
                    <input type="text" name="project_note" class="form-control" placeholder="Contoh: Project Indah Kiat">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_ppn" name="is_ppn" value="1">
                        <label class="form-check-label fw-bold" for="is_ppn">PPN 11%</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary m-0" style="font-weight: 600;">Detail Purchase Item</h5>
                <button type="button" class="btn btn-sm btn-success" id="btnAddItem">
                    <i class="bi bi-plus-circle"></i> Tambah Item
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="">
                        <tr>
                            <th>Product</th>
                            <th width="15%">Unit</th>
                            <th width="12%">Qty</th>
                            <th width="20%">Harga Satuan</th>
                            <th width="20%">Subtotal</th>
                            <th width="8%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <!-- Items will be appended here -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total Pembelian :</td>
                            <td colspan="2" class="fw-bold fs-6" id="subTotalDisplay">Rp0</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold text-danger">PPN 11% :</td>
                            <td colspan="2" class="fw-bold fs-6 text-danger" id="ppnDisplay">Rp0</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Grand Total :</td>
                            <td colspan="2" class="fw-bold fs-5 text-primary" id="grandTotalDisplay">Rp0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('purchasing.purchase-orders.index') }}" class="btn-outline-custom text-decoration-none">Batal</a>
                <button type="submit" class="btn-primary-custom" id="btnSimpan">Simpan Purchase Release</button>
            </div>
        </form>
    </div>
</div>

<!-- Template for new row -->
<template id="rowTemplate">
    <tr>
        <td>
            <select name="items[{idx}][product_id]" class="form-select product-select" required>
                <option value="">-- Pilih --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->product_code }} - {{ $product->product_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="items[{idx}][unit_id]" class="form-select unit-select" required>
                <option value="">-- Unit --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[{idx}][qty]" class="form-control input-qty" min="0.01" step="0.01" required>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="items[{idx}][price]" class="form-control input-price" min="1" step="1" required>
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control input-subtotal " readonly value="0">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="bi bi-trash"></i></button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script src="{{ asset('js/modules/product-autofill.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Gagal Disimpan',
            html: '{!! implode("<br>", $errors->all()) !!}'
        });
    @endif

    let itemIndex = 0;
    const tbody = document.getElementById('itemsTbody');
    const template = document.getElementById('rowTemplate').innerHTML;
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    // Initialize AutoFill module
    const autoFill = new ProductAutoFill({ container: '#itemsTbody' });

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function calculateTotal() {
        let subTotal = 0;
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            const price = parseFloat(row.querySelector('.input-price').value) || 0;
            const subtotal = qty * price;
            
            row.querySelector('.input-subtotal').value = formatRupiah(subtotal);
            subTotal += subtotal;
        });
        
        let ppnAmount = 0;
        if (document.getElementById('is_ppn').checked) {
            ppnAmount = subTotal * 0.11;
        }

        let grandTotal = subTotal + ppnAmount;
        
        document.getElementById('subTotalDisplay').innerText = 'Rp ' + formatRupiah(subTotal);
        document.getElementById('ppnDisplay').innerText = 'Rp ' + formatRupiah(ppnAmount);
        grandTotalDisplay.innerText = 'Rp ' + formatRupiah(grandTotal);
    }

    document.getElementById('is_ppn').addEventListener('change', calculateTotal);

    function addItemRow() {
        const tr = document.createElement('tr');
        tr.innerHTML = template.replaceAll('{idx}', itemIndex++);
        tbody.appendChild(tr);

        // Initialize Select2 on the new row
        $(tr).find('.product-select').select2({
            placeholder: '-- Pilih --',
            allowClear: true,
            width: '100%'
        });

        // Add event listeners to new row inputs
        tr.querySelector('.input-qty').addEventListener('input', calculateTotal);
        tr.querySelector('.input-price').addEventListener('input', calculateTotal);
        
        tr.querySelector('.btn-remove-item').addEventListener('click', function() {
            $(tr).find('.product-select').select2('destroy');
            tr.remove();
            calculateTotal();
        });
    }

    document.getElementById('btnAddItem').addEventListener('click', addItemRow);

    // Form submission validation
    document.getElementById('poForm').addEventListener('submit', function(e) {
        if (tbody.querySelectorAll('tr').length === 0) {
            e.preventDefault();
            notifyError('Pemberitahuan', 'Minimal terdapat satu item pembelian!');
        }
    });

    // Catch HTML5 validation failures (tooltips might be hidden by .table-responsive)
    document.getElementById('poForm').addEventListener('invalid', function(e) {
        e.preventDefault();
        notifyError('Validasi Gagal', 'Harap lengkapi field wajib yang masih kosong atau tidak valid (seperti Harga atau Qty).');
    }, true);

    // Add first row by default
    addItemRow();
});
</script>
@endpush
