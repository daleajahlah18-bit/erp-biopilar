@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h3 text-gray-800">Detail Payment: {{ $payment->payment_number }}</h2>
            <div>
                <a href="{{ route('purchasing.payments.pdf', $payment->id) }}" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Cetak PDF
                </a>
                <a href="{{ route('purchasing.payments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pembayaran</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%">No Payment</td>
                            <td>: <strong>{{ $payment->payment_number }}</strong></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Metode</td>
                            <td>: {{ $payment->payment_method }}</td>
                        </tr>
                        <tr>
                            <td>Nominal</td>
                            <td>: <strong>Rp {{ number_format($payment->payment_amount, 2, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td>: {{ $payment->notes ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Dibuat Oleh</td>
                            <td>: {{ $payment->creator->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Referensi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%">Goods Receipt</td>
                            <td>: <a href="{{ route('purchasing.goods-receipts.show', $payment->goods_receipt_id) }}">{{ $payment->goodsReceipt->gr_number }}</a></td>
                        </tr>
                        <tr>
                            <td>Purchase Release</td>
                            <td>: {{ $payment->goodsReceipt->purchaseOrder->po_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>: {{ $payment->goodsReceipt->purchaseOrder->supplier->supplier_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Total Pembelian</td>
                            <td>: Rp {{ number_format($payment->goodsReceipt->total_amount, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Sisa Tagihan Akhir</td>
                            <td>: <span class="text-danger">Rp {{ number_format($payment->goodsReceipt->remaining_amount, 2, ',', '.') }}</span></td>
                        </tr>
                        <tr>
                            <td>Status Tagihan</td>
                            <td>: 
                                @if($payment->goodsReceipt->payment_status == 'Paid')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($payment->goodsReceipt->payment_status == 'Partially Paid')
                                    <span class="badge badge-warning ">Partially Paid</span>
                                @else
                                    <span class="badge badge-danger">{{ $payment->goodsReceipt->payment_status }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
