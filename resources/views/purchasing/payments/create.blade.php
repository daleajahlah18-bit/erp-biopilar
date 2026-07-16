@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h3 text-gray-800">Tambah Pembayaran (Purchase Payment)</h2>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('purchasing.payments.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Goods Receipt</label>
                        <select name="goods_receipt_id" id="goods_receipt_id" class="form-control" required>
                            <option value="">-- Pilih GR (Unpaid / Partially Paid) --</option>
                            @foreach($receipts as $gr)
                                <option value="{{ $gr->id }}" {{ old('goods_receipt_id') == $gr->id ? 'selected' : '' }}>
                                    {{ $gr->gr_number }} - {{ $gr->purchaseOrder->supplier->supplier_name ?? '-' }} (Sisa: Rp {{ number_format($gr->remaining_amount, 0, '', '') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <hr>
                
                <div id="gr-info" style="display: none;">
                    <div class="row mb-3  p-3 rounded">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Nomor PR</p>
                            <h6 id="info_po_number">-</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Supplier</p>
                            <h6 id="info_supplier">-</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Tanggal GR</p>
                            <h6 id="info_gr_date">-</h6>
                        </div>
                        <div class="col-md-3 mt-3">
                            <p class="mb-1 text-muted small">Total Pembelian</p>
                            <h6 id="info_total_amount" class="font-weight-bold">-</h6>
                        </div>
                        <div class="col-md-3 mt-3">
                            <p class="mb-1 text-muted small">Sudah Dibayar</p>
                            <h6 id="info_total_paid" class="text-success font-weight-bold">-</h6>
                        </div>
                        <div class="col-md-3 mt-3">
                            <p class="mb-1 text-muted small">Sisa Hutang</p>
                            <h6 id="info_remaining" class="text-danger font-weight-bold">-</h6>
                        </div>
                        <div class="col-md-3 mt-3">
                            <p class="mb-1 text-muted small">Due Date (Term)</p>
                            <h6 id="info_due_date">-</h6>
                        </div>
                    </div>

                    <!-- Riwayat Pembayaran Sebelumnya -->
                    <div class="mb-4">
                        <h5>Riwayat Pembayaran</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No Payment</th>
                                        <th>Metode</th>
                                        <th>Nominal</th>
                                    </tr>
                                </thead>
                                <tbody id="history_body">
                                    <tr><td colspan="4" class="text-center">Belum ada</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label>Metode Pembayaran</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="Transfer Bank" {{ old('payment_method') == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Giro" {{ old('payment_method') == 'Giro' ? 'selected' : '' }}>Giro</option>
                            <option value="Lainnya" {{ old('payment_method') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Nominal Pembayaran (Rp)</label>
                        <input type="number" name="payment_amount" id="payment_amount" class="form-control" min="1" value="{{ old('payment_amount') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Keterangan</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" id="btn-submit">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grSelect = document.getElementById('goods_receipt_id');
    const grInfo = document.getElementById('gr-info');
    const inputPayment = document.getElementById('payment_amount');
    
    // Elements to update
    const poNumber = document.getElementById('info_po_number');
    const supplier = document.getElementById('info_supplier');
    const grDate = document.getElementById('info_gr_date');
    const totalAmount = document.getElementById('info_total_amount');
    const totalPaid = document.getElementById('info_total_paid');
    const remaining = document.getElementById('info_remaining');
    const dueDate = document.getElementById('info_due_date');
    const historyBody = document.getElementById('history_body');
    const btnSubmit = document.getElementById('btn-submit');

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
    };

    grSelect.addEventListener('change', function() {
        const grId = this.value;
        if (!grId) {
            grInfo.style.display = 'none';
            inputPayment.max = "";
            return;
        }

        fetch(`/purchasing/payments/api/gr-info/${grId}`)
            .then(res => res.json())
            .then(data => {
                grInfo.style.display = 'block';
                poNumber.textContent = data.purchase_order ? data.purchase_order.po_number : '-';
                supplier.textContent = data.purchase_order && data.purchase_order.supplier ? data.purchase_order.supplier.supplier_name : '-';
                grDate.textContent = data.receipt_date;
                
                totalAmount.textContent = formatRupiah(data.total_amount);
                totalPaid.textContent = formatRupiah(data.total_paid);
                remaining.textContent = formatRupiah(data.remaining_amount);
                
                let termText = data.terms_of_payment_days ? `${data.terms_of_payment_days} Hari` : '-';
                let dueText = data.due_date ? data.due_date : '-';
                dueDate.textContent = `${dueText} (${termText})`;

                // Update max value for payment input
                inputPayment.max = parseFloat(data.remaining_amount);
                
                // Jika ingin auto-fill:
                if(!inputPayment.value) {
                    inputPayment.value = parseFloat(data.remaining_amount);
                }

                // Render history
                historyBody.innerHTML = '';
                if(data.payments && data.payments.length > 0) {
                    data.payments.forEach(p => {
                        historyBody.innerHTML += `
                            <tr>
                                <td>${p.payment_date}</td>
                                <td>${p.payment_number}</td>
                                <td>${p.payment_method}</td>
                                <td>${formatRupiah(p.payment_amount)}</td>
                            </tr>
                        `;
                    });
                } else {
                    historyBody.innerHTML = '<tr><td colspan="4" class="text-center">Belum ada history pembayaran</td></tr>';
                }
            });
    });

    // Trigger on load if validation failed and old value exists
    if(grSelect.value) {
        grSelect.dispatchEvent(new Event('change'));
    }

    inputPayment.addEventListener('input', function() {
        let max = parseFloat(this.max);
        let val = parseFloat(this.value);
        if(val > max) {
            alert('Nominal pembayaran tidak boleh melebihi sisa hutang: ' + formatRupiah(max));
            this.value = max;
        }
    });
});
</script>
@endsection
