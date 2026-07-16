<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Receipt {{ $goods_receipt->gr_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2563eb; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; }
        .info-title { font-weight: bold; width: 130px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f8fafc; color: #333; }
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }
        .footer { margin-top: 50px; text-align: right; font-size: 12px; color: #666; }
    </style>
</head>
<body>

    <div class="header">
        <h1>PT BIO PILAR UTAMA</h1>
        <p>Jl. Contoh Alamat No. 123, Jakarta - Indonesia | Telp: (021) 1234567</p>
    </div>

    <h2 style="text-align: center; margin-bottom: 20px;">GOODS RECEIPT (TANDA TERIMA BARANG)</h2>

    <table class="info-table">
        <tr>
            <td width="50%">
                <table>
                    <tr><td class="info-title">Nomor GR</td><td>: <strong>{{ $goods_receipt->gr_number }}</strong></td></tr>
                    <tr><td class="info-title">Tanggal Terima</td><td>: {{ \Carbon\Carbon::parse($goods_receipt->receipt_date)->format('d F Y') }}</td></tr>
                    <tr><td class="info-title">Gudang Tujuan</td><td>: {{ $goods_receipt->warehouse->warehouse_name ?? '-' }}</td></tr>
                    <tr><td class="info-title">Penerima Barang</td><td>: {{ $goods_receipt->received_by }}</td></tr>
                </table>
            </td>
            <td width="50%">
                <table>
                    <tr><td class="info-title">Referensi PO</td><td>: <strong>{{ $goods_receipt->purchaseOrder->po_number ?? '-' }}</strong></td></tr>
                    <tr><td class="info-title">Supplier</td><td>: {{ $goods_receipt->purchaseOrder->supplier->supplier_name ?? '-' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="45%">Product</th>
                <th width="15%">Unit</th>
                <th width="15%" class="text-end">Qty PO</th>
                <th width="20%" class="text-end">Qty Diterima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goods_receipt->details as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product->product_name ?? '-' }}</td>
                <td>{{ $item->product->unit->unit_name ?? '-' }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity_order, 2, ',', '.'), '0'), ',') }}</td>
                <td class="text-end" style="font-weight: bold;">{{ rtrim(rtrim(number_format($item->quantity_received, 2, ',', '.'), '0'), ',') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dibuat oleh: {{ $goods_receipt->creator->name ?? 'System' }}</p>
        <p>Tanggal Cetak Dokumen: {{ date('d/m/Y H:i') }}</p>
    </div>

</body>
</html>
