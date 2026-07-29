<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PROJECT INFORMATION SHEET - {{ $project->project_name }}</title>
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
        .info-table { border: none; width: 100%; margin-bottom: 10px; }
        .info-table td { border: none; padding: 2px 4px; vertical-align: top; }
        .info-label { width: 150px; font-weight: bold; }
        .info-colon { width: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo11.png') }}" class="logo">
        <div class="title">PROJECT INFORMATION SHEET</div>
        <div>{{ $project->project_name }}</div>
    </div>

    <div class="section-title">PROJECT INFORMATION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Project Name</td>
            <td class="info-colon">:</td>
            <td>{{ $project->project_name }}</td>
        </tr>
        <tr>
            <td class="info-label">Project Status</td>
            <td class="info-colon">:</td>
            <td>{{ $project->project_status ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Field of Work</td>
            <td class="info-colon">:</td>
            <td>{{ $project->field_of_work ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Project Address</td>
            <td class="info-colon">:</td>
            <td>{{ $project->project_address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Start Date</td>
            <td class="info-colon">:</td>
            <td>{{ $project->project_start_date ? $project->project_start_date->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">End Date</td>
            <td class="info-colon">:</td>
            <td>{{ $project->project_end_date ? $project->project_end_date->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Work Duration</td>
            <td class="info-colon">:</td>
            <td>
                @if($project->project_start_date && $project->project_end_date)
                    {{ $project->project_start_date->diffInDays($project->project_end_date) }} Days
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">CLIENT INFORMATION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Client Name</td>
            <td class="info-colon">:</td>
            <td>{{ $project->client_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Client PIC</td>
            <td class="info-colon">:</td>
            <td>{{ $project->person_in_charge ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Client PO Number</td>
            <td class="info-colon">:</td>
            <td>{{ $project->client_po_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Client PO Date</td>
            <td class="info-colon">:</td>
            <td>{{ $project->client_po_date ? $project->client_po_date->format('d F Y') : '-' }}</td>
        </tr>
    </table>

    <div class="section-title">FINANCIAL SUMMARY</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Project Value</td>
            <td class="info-colon">:</td>
            <td>Rp {{ number_format($project->project_value, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">HPP</td>
            <td class="info-colon">:</td>
            <td>{{ $project->hpp ? 'Rp ' . number_format($project->hpp, 0, ',', '.') : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Project Margin</td>
            <td class="info-colon">:</td>
            <td>{{ $project->margin !== null ? 'Rp ' . number_format($project->margin, 0, ',', '.') : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Margin Percentage</td>
            <td class="info-colon">:</td>
            <td>{{ $project->margin_percentage !== null ? number_format($project->margin_percentage, 2, ',', '.') . '%' : '-' }}</td>
        </tr>
    </table>

    <div class="section-title">PAYMENT SCHEDULE</div>
    @php
        $paymentTerms = $project->projectPaymentTerms;
    @endphp
    @if($paymentTerms && $paymentTerms->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Term Name</th>
                    <th class="text-right">Percentage</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentTerms as $term)
                <tr>
                    <td>{{ $term->top_type }}</td>
                    <td class="text-right">{{ number_format($term->percentage, 2, ',', '.') }}%</td>
                    <td class="text-right">Rp {{ number_format($term->nominal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div>-</div>
    @endif

    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #666;">
        Generated Date: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}<br>
        Generated By: {{ auth()->user()->name ?? 'System' }}
    </div>

</body>
</html>
