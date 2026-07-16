<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt {{ $payment->payment_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .box { border: 1px solid #000; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>OFFICIAL RECEIPT / KUITANSI</h2>
        <h3>PT BIO PILAR UTAMA</h3>
    </div>
    
    <div class="box">
        <table style="width: 100%;">
            <tr><td width="25%"><strong>No Tanda Terima</strong></td><td>: {{ $payment->payment_number }}</td></tr>
            <tr><td><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($payment->payment_date)) }}</td></tr>
            <tr><td><strong>Telah Terima Dari</strong></td><td>: {{ $payment->salesInvoice->salesOrder->project->client_name ?? ($payment->salesInvoice->salesOrder->customer->customer_name ?? '-') }}</td></tr>
            <tr><td><strong>Uang Sejumlah</strong></td><td>: <strong>Rp {{ number_format($payment->payment_amount, 2) }}</strong></td></tr>
            <tr><td><strong>Untuk Pembayaran</strong></td><td>: 
                Tagihan Invoice {{ $payment->salesInvoice->invoice_number }} 
                @if($payment->projectPaymentTerm)
                    <br>&nbsp;&nbsp;Termin: {{ $payment->projectPaymentTerm->top_type }} ({{ number_format($payment->projectPaymentTerm->percentage, 2) }}%)
                @endif
            </td></tr>
            <tr><td><strong>Metode Pembayaran</strong></td><td>: {{ $payment->payment_method }}</td></tr>
            <tr><td><strong>Keterangan</strong></td><td>: {{ $payment->notes }}</td></tr>
        </table>
    </div>

    <table style="width:100%; text-align:center; margin-top:50px;">
        <tr>
            <td width="50%">Penyetor / Customer<br><br><br><br>( ............................ )</td>
            <td width="50%">Penerima / PT Bio Pilar<br><br><br><br>( ............................ )</td>
        </tr>
    </table>
</body>
</html>
