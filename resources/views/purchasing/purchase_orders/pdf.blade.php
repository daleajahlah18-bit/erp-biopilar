<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO_{{ $purchaseOrder->po_number }}</title>
    <style>
        @font-face {
            font-family: 'Bauhaus 93';
            src: url('{{ public_path("fonts/bauhs93.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            margin: 30px;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-name {
            font-size: 30px;
            font-weight: bold;
            color: #12a84a;
            margin-bottom: 5px;
            font-family: 'Bauhaus 93', sans-serif;
        }

        .company-address {
            color: #12a84a;
            font-size: 12px;
            line-height: 1.4;
        }

        .supplier-info {
            font-size: 12px;
            line-height: 1.4;
        }

        .doc-title {
            color: #000080;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 5px;
        }

        .doc-meta {
            color: #000080;
            line-height: 1.4;
            margin-bottom: 15px;
        }

        .greeting {
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .PR-table {
            width: 100%;
            border-collapse: collapse;
        }

        .PR-table th,
        .PR-table td {
            border: 1px solid black;
            padding: 6px;
        }

        .PR-table th {
            background: #efefef;
            text-align: center;
            font-weight: bold;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .signature-table {
            width: 100%;
            margin-top: 60px;
            text-align: center;
        }
        
        .signature-table td {
            width: 33.33%;
            vertical-align: bottom;
            height: 100px; /* Space for signature */
        }
        
        .signature-title {
            vertical-align: top !important;
            height: auto !important;
        }
        
        .date-right {
            text-align: right;
            margin-top: 20px;
        }
        
        /* Stamp from previous request if needed */
        .stamp {
            position: absolute;
            opacity: 0.25;
            border: 3px solid green;
            color: green;
            padding: 20px;
            border-radius: 50%;
            transform: rotate(-20deg);
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            width: 100px;
            left: 50%;
            margin-left: -70px;
            z-index: -1;
        }
        
        .terbilang-row {
            font-style: italic;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 150px; text-align: left; padding-right: 10px;">
                <img src="{{ public_path('logo11.png') }}" style="max-width: 120px;" alt="Logo">
            </td>
            <td style="vertical-align: middle;" colspan="2">
                <div class="company-name">PT. Bio Pilar Utama</div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding-right: 10px;">
                <div class="company-address">
                    Jl. Kalodran Pamong KM 1<br>
                    Kel. Teritih Kec Walantaka<br>
                    Kota Serang - Banten<br>
                    42183
                </div>
            </td>
            <td style="width: 45%;"></td>
            <td style="width: 40%; vertical-align: top;" class="supplier-info">
                Kepada Yth,<br>
                {{ $purchaseOrder->supplier->supplier_name ?? '-' }}<br>
                Up. {{ $purchaseOrder->supplier->contact_person ?? '-' }}<br>
                Phone : {{ $purchaseOrder->supplier->supplier_phone ?? '-' }}<br>
                Fax : -<br>
                Email : {{ $purchaseOrder->supplier->supplier_email ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="doc-title">SURAT PEMBELIAN (Purchase Release)</div>
    <div class="doc-meta">
        Date &nbsp;&nbsp;&nbsp;&nbsp;: {{ $purchaseOrder->po_date ? \Carbon\Carbon::parse($purchaseOrder->po_date)->format('d F Y') : '-' }}<br>
        NO. PR : {{ $purchaseOrder->po_number }}
    </div>

    <div class="greeting">
        Dengan hormat,<br>
        Kami mohon dipersiapkan barang sebagai berikut dibawah ini :
    </div>

    <table class="PR-table">
        <thead>
            <tr>
                <th rowspan="2" width="5%">No</th>
                <th rowspan="2" width="45%">NAMA BARANG</th>
                <th rowspan="2" width="8%">QTY</th>
                <th rowspan="2" width="10%">UNIT</th>
                <th colspan="2" width="32%">HARGA (Rp.)</th>
            </tr>
            <tr>
                <th width="16%">SATUAN</th>
                <th width="16%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrder->details as $detail)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $detail->product->product_name ?? '-' }}</td>
                <td class="text-center">{{ $detail->quantity }}</td>
                <td class="text-center">{{ $detail->unit->unit_name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada item</td>
            </tr>
            @endforelse
            
            @php
                $total = $purchaseOrder->total_amount ?? $purchaseOrder->details->sum('subtotal');
                $ppn = $purchaseOrder->is_ppn ? $purchaseOrder->ppn_amount : 0;
                $grandTotal = $purchaseOrder->grand_total ?: $total;
            @endphp
            
            <tr>
                <td colspan="4" class="text-center font-bold">TOTAL</td>
                <td colspan="2" class="text-right font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            @if($purchaseOrder->is_ppn)
            <tr>
                <td colspan="4" class="text-center font-bold">PPN {{ number_format($purchaseOrder->ppn_percentage, 0) }}%</td>
                <td colspan="2" class="text-right font-bold">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="4" class="text-center font-bold">GRAND TOTAL</td>
                <td colspan="2" class="text-right font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="6" class="terbilang-row">
                    Terbilang : "{{ \App\Helpers\TerbilangHelper::terbilang($grandTotal) }}"
                </td>
            </tr>
        </tbody>
    </table>

    <div class="date-right">
        Serang, {{ $purchaseOrder->po_date ? \Carbon\Carbon::parse($purchaseOrder->po_date)->format('d F Y') : date('d F Y') }}
    </div>

    <table class="signature-table">
        <tr>
            <td class="signature-title font-bold">
                VENDOR<br>
                <div style="margin-top:5px; font-weight:normal;">{{ $purchaseOrder->supplier->supplier_name ?? '-' }}</div>
            </td>
            <td class="signature-title font-bold" style="position:relative;">
                APPROVE<br>
            </td>
            <td class="signature-title font-bold">
                PREPARED BY
            </td>
        </tr>
        <tr>
            <td class="font-bold">
                {{ $purchaseOrder->supplier->pic ?? '-' }}
            </td>
            <td class="font-bold">
                SUPRATIKNO
            </td>
            <td class="font-bold">
                {{ auth()->user() ? auth()->user()->name : ($purchaseOrder->creator->name ?? '-') }}
            </td>
        </tr>
    </table>

</body>
</html>
