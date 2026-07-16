@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h3 text-gray-800">Purchase Payments</h2>
            <a href="{{ route('purchasing.payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pembayaran
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="">
                        <tr>
                            <th>No Payment</th>
                            <th>@sortablelink('created_at', 'Tanggal')</th>
                            <th>@sortablelink('gr_number', 'GR Number')</th>
                            <th>@sortablelink('supplier', 'Supplier')</th>
                            <th>@sortablelink('metode', 'Metode')</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->payment_date }}</td>
                            <td>
                                @if($payment->goodsReceipt)
                                    <a href="{{ route('purchasing.goods-receipts.show', $payment->goods_receipt_id) }}">
                                        {{ $payment->goodsReceipt->gr_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $payment->goodsReceipt->purchaseOrder->supplier->supplier_name ?? '-' }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>Rp {{ number_format($payment->payment_amount, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('purchasing.payments.show', $payment->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data pembayaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
