<!DOCTYPE html>
<html>
<head>
    <title>Sales Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 5px; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVOICE</h2>
        <h3>PT BIO PILAR UTAMA</h3>
    </div>
    <table style="width:100%; margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <strong>No Invoice:</strong> {{ $invoice->invoice_number }}<br>
                <strong>No SO:</strong> {{ $invoice->salesOrder->sales_order_number }}<br>
                <strong>Tanggal:</strong> {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}<br>
                @php
                    $dueDate = \Carbon\Carbon::parse($invoice->invoice_date)->addDays($invoice->terms_of_payment_days)->format('d/m/Y');
                @endphp
                <strong>Jatuh Tempo:</strong> {{ $dueDate }}
            </td>
            <td width="50%" style="text-align:right;">
                <strong>Project / Kepada Yth:</strong><br>
                {{ $invoice->salesOrder->project->project_name ?? ($invoice->salesOrder->customer->customer_name ?? '-') }}<br>
                {{ $invoice->salesOrder->project->client_name ?? ($invoice->salesOrder->customer->customer_address ?? '-') }}<br>
                {{ $invoice->salesOrder->project->project_address ?? ($invoice->salesOrder->customer->customer_phone ?? '-') }}
            </td>
        </tr>
    </table>

    @if($invoice->details && $invoice->details->count() > 0)
    <h4 style="margin-bottom: 5px;">DETAIL PRODUK</h4>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->product_name }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ $item->unit->unit_name }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Total Produk</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($invoice->details->sum('subtotal'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($invoice->services && $invoice->services->count() > 0)
    <h4 style="margin-bottom: 5px; margin-top: 15px;">DETAIL JASA</h4>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Jasa</th>
                <th>Qty</th>
                <th>Keterangan</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->services as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->service_name }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ $item->notes }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Total Jasa</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($invoice->services->sum('subtotal'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <table style="width:100%; margin-top: 20px;">
        <tr>
            <td width="70%"></td>
            <td width="30%">
                <table class="table" style="width: 100%;">
                    <tr>
                        <td class="text-end" style="border:none;"><strong>Grand Total:</strong></td>
                        <td class="text-end" style="border:none;"><strong>Rp {{ number_format($invoice->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
