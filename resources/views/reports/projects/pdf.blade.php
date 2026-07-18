<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Report - {{ $project->project_name }}</title>
    <style>
        @page { margin: 25px; }
        body { font-family: "Helvetica", "Arial", sans-serif; font-size: 11px; color: #333; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .logo { max-width: 150px; }
        .title { font-size: 16px; font-weight: bold; margin: 5px 0; color: #28a745; }
        .section-title { font-size: 13px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { width: 48%; display: inline-block; vertical-align: top; }
        .summary-mini { border: 1px solid #ccc; padding: 4px; text-align: center; background-color: #fafafa; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo11.png') }}" class="logo">
        <div class="title">PROJECT REPORT</div>
        <div>{{ $project->project_name }}</div>
    </div>

    <div class="section-title">Section A : Project Information</div>
    <div>
        <div class="summary-box">
            <strong>Client Name:</strong> {{ $projectInfo->client_name }}<br>
            <strong>PR Date:</strong> {{ $projectInfo->client_po_date ? $projectInfo->client_po_date->format('d M Y') : '-' }}<br>
            <strong>Status:</strong> {{ $projectInfo->project_status }}
        </div>
    </div>

    @php
        $terms = $projectInfo->projectPaymentTerms;
        $hasSchedule = $terms && $terms->count() > 0;
        $projectVal = $projectInfo->project_value;
        $totTermin = 0;
        $totPaid = 0;
        $totOuts = 0;
        if($hasSchedule) {
            foreach($terms as $term) {
                $totTermin += $term->nominal;
                $totPaid += $term->total_paid;
                $totOuts += $term->remaining_amount;
            }
        }
        $progPct = $projectVal > 0 ? ($totPaid / $projectVal) * 100 : 0;
    @endphp

    <div class="section-title">Section B : Payment Progress</div>
    @if(!$hasSchedule)
        <p style="text-align: center; font-style: italic;">No Payment Schedule has been configured for this Project.</p>
    @else
        <table style="border: none; margin-bottom: 5px;">
            <tr>
                <td style="border: none;" width="20%">
                    <div class="summary-mini">
                        <small>Project Value</small><br>
                        <strong>Rp {{ number_format($projectVal, 0, ',', '.') }}</strong>
                    </div>
                </td>
                <td style="border: none;" width="20%">
                    <div class="summary-mini">
                        <small>Total Termin</small><br>
                        <strong>Rp {{ number_format($totTermin, 0, ',', '.') }}</strong>
                    </div>
                </td>
                <td style="border: none;" width="20%">
                    <div class="summary-mini">
                        <small>Total Paid</small><br>
                        <strong style="color: green;">Rp {{ number_format($totPaid, 0, ',', '.') }}</strong>
                    </div>
                </td>
                <td style="border: none;" width="20%">
                    <div class="summary-mini">
                        <small>Outstanding</small><br>
                        <strong style="color: red;">Rp {{ number_format($totOuts, 0, ',', '.') }}</strong>
                    </div>
                </td>
                <td style="border: none;" width="20%">
                    <div class="summary-mini">
                        <small>Progress</small><br>
                        <strong>{{ number_format($progPct, 2) }}%</strong>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th>TOP</th>
                    <th class="text-center">%</th>
                    <th>Termin</th>
                    <th class="text-right">Nominal (Rp)</th>
                    <th class="text-right">Sudah Dibayar (Rp)</th>
                    <th class="text-right">Sisa Tagihan (Rp)</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terms as $idx => $term)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $term->top_type }}</td>
                    <td class="text-center">{{ number_format($term->percentage, 2) }}%</td>
                    <td>{{ $term->term_value }} {{ $term->term_unit }}</td>
                    <td class="text-right">{{ number_format($term->nominal, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: green;">{{ number_format($term->total_paid, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: red;">{{ number_format($term->remaining_amount, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $term->payment_status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Section C : Sales Order History</div>
    <table>
        <thead>
            <tr>
                <th>SO Number</th>
                <th>Date</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesHistory as $sale)
            <tr>
                <td>{{ $sale->sales_order_number }}</td>
                <td>{{ \Carbon\Carbon::parse($sale->sales_order_date)->format('d M Y') }}</td>
                <td>{{ $sale->status }}</td>
                <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @if(count($salesHistory) == 0)
            <tr><td colspan="4" class="text-center">No sales records</td></tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Section D : Material Usage Summary</div>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Code</th>
                <th class="text-center">Qty Used</th>
                <th>Unit</th>
                <th class="text-right">Material Cost (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedUsage ?? [] as $category => $usages)
                <tr style="background-color: #e9ecef;">
                    <td colspan="5" style="font-weight: bold; text-transform: uppercase;">{{ $category }}</td>
                </tr>
                @foreach($usages as $usage)
                <tr>
                    <td>{{ $usage['product_name'] }}</td>
                    <td>{{ $usage['product_code'] }}</td>
                    <td class="text-center">{{ number_format($usage['quantity'], 2, ',', '.') }}</td>
                    <td>{{ $usage['unit'] }}</td>
                    <td class="text-right">{{ number_format($usage['material_cost'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #f8f9fa;">
                    <td colspan="4" class="text-right" style="font-weight: bold;">Subtotal {{ $category }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format(collect($usages)->sum('material_cost'), 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            @if(count($groupedUsage ?? []) > 0)
                <tr>
                    <td colspan="4" class="text-right" style="font-weight: bold; color: #4e73df;">Grand Total Material</td>
                    <td class="text-right" style="font-weight: bold; color: #4e73df;">Rp {{ number_format($grandTotalMaterial ?? 0, 0, ',', '.') }}</td>
                </tr>
            @else
                <tr><td colspan="5" class="text-center">No material usage records</td></tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Section E : Service Cost Summary</div>
    <table>
        <thead>
            <tr>
                <th>Service Name</th>
                <th class="text-center">Total Quantity Used</th>
                <th class="text-right">Total Revenue (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceUsage as $service)
            <tr>
                <td>{{ $service['service_name'] }}</td>
                <td class="text-center">{{ number_format($service['total_quantity'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($service['total_subtotal'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
            @if(count($serviceUsage) == 0)
            <tr><td colspan="3" class="text-center">No service usage records</td></tr>
            @endif
        </tbody>
    </table>



    <div class="section-title">Section F : Financial Summary</div>
    <table style="width: 50%;">
        <tr>
            <td><strong>Project Value</strong></td>
            <td class="text-right">Rp {{ number_format($financialSummary['project_value'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total HPP</strong></td>
            <td class="text-right">Rp {{ number_format($financialSummary['total_hpp'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Margin</strong></td>
            <td class="text-right">Rp {{ number_format($financialSummary['margin'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Margin %</strong></td>
            <td class="text-right">{{ number_format($financialSummary['margin_percentage'], 2, ',', '.') }}%</td>
        </tr>
    </table>

</body>
</html>
