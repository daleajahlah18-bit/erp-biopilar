<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Fabrication - {{ $projectProduction->project_production_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .subtitle { font-size: 14px; margin: 5px 0 0; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-label { width: 120px; font-weight: bold; }
        
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .item-table th, .item-table td { border: 1px solid #ddd; padding: 8px; }
        .item-table th { background-color: #f8f9fa; text-align: center; }
        .item-table td.qty { text-align: right; }
        .item-table td.center { text-align: center; }
        
        .footer { margin-top: 50px; width: 100%; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-line { margin-top: 60px; border-top: 1px solid #333; padding-top: 5px; }
        .print-info { font-size: 10px; color: #777; margin-top: 50px; clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">PT BIO PILAR UTAMA</h1>
        <p class="subtitle">PROJECT FABRICATION (PEMAKAIAN BARANG)</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">No Dokumen</td>
            <td>: {{ $projectProduction->project_production_number }}</td>
            <td class="info-label">Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($projectProduction->production_date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Project</td>
            <td>: {{ $projectProduction->project->project_name ?? '-' }}</td>
            <td class="info-label">Gudang Asal</td>
            <td>: {{ $projectProduction->warehouse->warehouse_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Keterangan</td>
            <td colspan="3">: {{ $projectProduction->notes ?: '-' }}</td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-bottom: 5px;">A. PEMAKAIAN MATERIAL</div>
    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Produk / Barang</th>
                <th width="15%">Qty Dipakai</th>
                <th width="10%">Unit</th>
                <th width="15%">HPP Satuan</th>
                <th width="20%">Material Cost</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMaterial = 0; @endphp
            @forelse($projectProduction->details as $index => $detail)
            @php 
                $subtotal = $detail->quantity * $detail->bom_hpp;
                $totalMaterial += $subtotal;
            @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>
                    @if($detail->billOfMaterial)
                        <span style="font-size: 10px; background-color: #eee; padding: 2px 4px; border-radius: 3px;">BOM: {{ $detail->billOfMaterial->bom_name }}</span><br>
                    @endif
                    <strong>{{ $detail->product->product_name ?? '-' }}</strong><br>
                    <span style="color: #666; font-size: 11px;">{{ $detail->product->product_code ?? '-' }}</span>
                </td>
                <td class="qty">{{ number_format($detail->quantity, 2, ',', '.') }}</td>
                <td class="center">{{ $detail->unit->unit_name ?? '' }}</td>
                <td class="qty">Rp {{ number_format($detail->bom_hpp, 0, ',', '.') }}</td>
                <td class="qty">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="center">Tidak ada material</td></tr>
            @endforelse
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">Total Material Cost:</td>
                <td class="qty" style="font-weight: bold;">Rp {{ number_format($totalMaterial, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="font-weight: bold; margin-bottom: 5px; margin-top: 20px;">B. BIAYA JASA (SERVICES)</div>
    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Nama Jasa</th>
                <th width="15%">Qty</th>
                <th width="15%">Harga Satuan</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $totalService = 0; @endphp
            @forelse($projectProduction->services as $index => $service)
            @php $totalService += $service->subtotal; @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $service->service_name }}</td>
                <td class="qty">{{ number_format($service->quantity, 2, ',', '.') }}</td>
                <td class="qty">Rp {{ number_format($service->unit_price, 0, ',', '.') }}</td>
                <td class="qty">Rp {{ number_format($service->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="center">Tidak ada jasa</td></tr>
            @endforelse
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">Total Service Cost:</td>
                <td class="qty" style="font-weight: bold;">Rp {{ number_format($totalService, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <table class="info-table" style="margin-top: 20px;">
        <tr>
            <td style="width: 70%;"></td>
            <td style="width: 15%; font-weight: bold; font-size: 14px;">GRAND TOTAL HPP :</td>
            <td style="width: 15%; font-weight: bold; font-size: 14px; text-align: right;">Rp {{ number_format($totalMaterial + $totalService, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <div class="signature-box">
            Dibuat Oleh,
            <div class="signature-line">
                {{ $projectProduction->creator->name ?? 'Admin' }}
            </div>
        </div>
        
        <div class="print-info">
            Dicetak pada: {{ now()->format('d M Y H:i:s') }}
        </div>
    </div>

</body>
</html>
