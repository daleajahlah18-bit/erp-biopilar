<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Asset Report - {{ $type }}</title>
    <style>
        @font-face {
            font-family: 'Bauhaus 93';
            src: url('{{ public_path("fonts/bauhs93.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @page { margin: 30px; }
        body { font-family: sans-serif; font-size: 11px; color: #000; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 30px; font-weight: bold; color: #12a84a; margin-bottom: 5px; font-family: 'Bauhaus 93', sans-serif; }
        .company-address { color: #12a84a; font-size: 12px; line-height: 1.4; }
        .report-meta { font-size: 12px; line-height: 1.4; text-align: right; }
        .doc-title { color: #000080; font-weight: bold; margin-top: 25px; margin-bottom: 5px; text-transform: uppercase; font-size: 14px; }
        .doc-meta-info { color: #000080; line-height: 1.4; margin-bottom: 15px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { border: 1px solid black; padding: 5px; }
        .data-table th { background: #efefef; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .section-header { background: #e0e0e0; font-weight: bold; text-align: left; }
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
                    Jl. Kalodran Pamong KM 1<br>Kel. Teritih Kec Walantaka<br>Kota Serang - Banten<br>42183
                </div>
            </td>
            <td style="width: 30%;"></td>
            <td style="width: 55%; vertical-align: top;" class="report-meta">
                <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d F Y') }}<br>
                <strong>Tipe Laporan:</strong> {{ str_replace('_', ' ', strtoupper($type)) }}<br>
                <strong>Dicetak Oleh:</strong> {{ auth()->user()->name ?? 'System' }}
            </td>
        </tr>
    </table>

    <div class="doc-title">Laporan Master Asset - {{ str_replace('_', ' ', strtoupper($type)) }}</div>
    <div class="doc-meta-info">
        Data ini disajikan secara Real-Time menggunakan Dynamic Depreciation Engine.
    </div>

    @foreach($assets as $asset)
    <table class="data-table" style="margin-bottom: 30px;">
        <tr>
            <th colspan="4" class="section-header" style="background:#000080; color:white;">{{ $asset->asset_code }} - {{ $asset->asset_name }}</th>
        </tr>
        <tr>
            <th width="25%">Category</th>
            <td width="25%">{{ $asset->category->category_name ?? '-' }}</td>
            <th width="25%">Location</th>
            <td width="25%">{{ $asset->location }} (PIC: {{ $asset->responsible_person }})</td>
        </tr>
        <tr>
            <th>Acquisition Cost</th>
            <td colspan="3" class="text-right fw-bold">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th colspan="2" class="section-header text-center" style="background: #e6f2ff;">Commercial Book</th>
            <th colspan="2" class="section-header text-center" style="background: #e6ffe6;">Fiscal Book</th>
        </tr>
        <tr>
            <th>Method</th>
            <td>{{ $asset->commercial_method }}</td>
            <th>Method</th>
            <td>{{ $asset->fiscal_method }}</td>
        </tr>
        <tr>
            <th>Remaining Life</th>
            <td>{{ $asset->commercial_remaining_life }} / {{ $asset->commercial_useful_life }} Months</td>
            <th>Remaining Life</th>
            <td>{{ $asset->fiscal_remaining_life }} / {{ $asset->fiscal_useful_life }} Months</td>
        </tr>
        <tr>
            <th>Accumulated Depr.</th>
            <td class="text-right text-danger">Rp {{ number_format($asset->commercial_accumulated_depreciation, 0, ',', '.') }}</td>
            <th>Accumulated Depr.</th>
            <td class="text-right text-danger">Rp {{ number_format($asset->fiscal_accumulated_depreciation, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Net Book Value</th>
            <td class="text-right" style="font-weight:bold; font-size:12px;">Rp {{ number_format($asset->commercial_book_value, 0, ',', '.') }}</td>
            <th>Net Book Value</th>
            <td class="text-right" style="font-weight:bold; font-size:12px;">Rp {{ number_format($asset->fiscal_book_value, 0, ',', '.') }}</td>
        </tr>
    </table>
    @endforeach

    <script>window.print();</script>
</body>
</html>
