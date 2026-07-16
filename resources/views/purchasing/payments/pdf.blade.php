<!DOCTYPE html>
<html>
<head>
    <title>Purchase Payment - {{ $payment->payment_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .details { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .details td { padding: 5px; }
        .title { font-size: 20px; font-weight: bold; }
        .amount-box { border: 1px solid #000; padding: 10px; text-align: center; background-color: #f8f9fa; }
        .amount-box h3 { margin: 0; color: #28a745; }
        .footer { margin-top: 50px; text-align: right; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">PAYMENT RECEIPT (KUITANSI PEMBAYARAN)</div>
        <p>PT BIO PILAR UTAMA</p>
    </div>

    <table class="details">
        <tr>
            <td width="20%"><strong>No. Payment</strong></td>
            <td width="30%">: {{ $payment->payment_number }}</td>
            <td width="20%"><strong>Tanggal</strong></td>
            <td width="30%">: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td><strong>Supplier</strong></td>
            <td>: {{ $payment->goodsReceipt->purchaseOrder->supplier->supplier_name ?? '-' }}</td>
            <td><strong>Metode Bayar</strong></td>
            <td>: {{ $payment->payment_method }}</td>
        </tr>
        <tr>
            <td><strong>NO. PR</strong></td>
            <td>: {{ $payment->goodsReceipt->purchaseOrder->po_number ?? '-' }}</td>
            <td><strong>No. GR</strong></td>
            <td>: {{ $payment->goodsReceipt->gr_number }}</td>
        </tr>
        <tr>
            <td><strong>Keterangan</strong></td>
            <td colspan="3">: {{ $payment->notes ?? '-' }}</td>
        </tr>
    </table>

    <br>

    <div class="amount-box">
        <p>Telah dibayarkan sejumlah:</p>
        <h3>Rp {{ number_format($payment->payment_amount, 2, ',', '.') }}</h3>
    </div>

    <br><br>

    <table class="details">
        <tr>
            <td width="50%"><strong>Status Tagihan Akhir GR:</strong></td>
            <td></td>
        </tr>
        <tr>
            <td>Total Pembelian GR</td>
            <td>: Rp {{ number_format($payment->goodsReceipt->total_amount, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Sisa Tagihan (Setelah Pembayaran Ini)</td>
            <td>: Rp {{ number_format($payment->goodsReceipt->remaining_amount, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: {{ $payment->goodsReceipt->payment_status }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Dibuat Oleh,</p>
        <br><br><br>
        <p><strong>{{ $payment->creator->name ?? 'Finance Dept' }}</strong></p>
    </div>

</body>
</html>
