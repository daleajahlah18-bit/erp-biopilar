@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h3 text-gray-800">Detail Purchase Payable</h2>
            <a href="{{ route('purchasing.payables.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @php
        $isOverdue = ($goods_receipt->remaining_amount > 0 && $goods_receipt->due_date && $goods_receipt->due_date < now()->toDateString());
    @endphp

    <div class="row">
        <!-- Info Tagihan -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Tagihan</h6>
                    @if($isOverdue)
                        <span class="badge badge-danger  p-2">OVERDUE</span>
                    @elseif($goods_receipt->payment_status == 'Paid')
                        <span class="badge badge-success  p-2">LUNAS</span>
                    @else
                        <span class="badge badge-warning  p-2">{{ strtoupper($goods_receipt->payment_status) }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%">Goods Receipt</td>
                            <td>: <a href="{{ route('purchasing.goods-receipts.show', $goods_receipt->id) }}">{{ $goods_receipt->gr_number }}</a></td>
                        </tr>
                        <tr>
                            <td>Purchase Release</td>
                            <td>: {{ $goods_receipt->purchaseOrder->po_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>: <strong>{{ $goods_receipt->purchaseOrder->supplier->supplier_name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Tanggal GR</td>
                            <td>: {{ \Carbon\Carbon::parse($goods_receipt->receipt_date)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td>Due Date</td>
                            <td>: <span class="{{ $isOverdue ? 'text-danger font-weight-bold' : '' }}">
                                {{ $goods_receipt->due_date ? \Carbon\Carbon::parse($goods_receipt->due_date)->format('d F Y') : '-' }}
                                ({{ $goods_receipt->terms_of_payment_days }} Hari)
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><hr></td>
                        </tr>
                        <tr>
                            <td>Total Pembelian</td>
                            <td>: Rp {{ number_format($goods_receipt->total_amount, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Total Dibayar</td>
                            <td class="text-success">: Rp {{ number_format($goods_receipt->total_paid, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Sisa Hutang</strong></td>
                            <td class="text-danger"><strong>: Rp {{ number_format($goods_receipt->remaining_amount, 2, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Histori Pembayaran -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Histori Pembayaran (Cicilan)</h6>
                    @if($goods_receipt->remaining_amount > 0)
                        <a href="{{ route('purchasing.payments.create') }}?gr_id={{ $goods_receipt->id }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Cicil Baru
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Payment</th>
                                    <th>Metode</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goods_receipt->payments as $pay)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pay->payment_date)->format('d/m/Y') }}</td>
                                    <td><a href="{{ route('purchasing.payments.show', $pay->id) }}">{{ $pay->payment_number }}</a></td>
                                    <td>{{ $pay->payment_method }}</td>
                                    <td class="text-success">Rp {{ number_format($pay->payment_amount, 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada transaksi pembayaran.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
