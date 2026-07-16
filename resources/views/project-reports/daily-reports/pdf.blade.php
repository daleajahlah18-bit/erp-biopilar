<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report - {{ $dailyReport->report_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        
        .no-border { border: none !important; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .bg-gray { background-color: #f0f0f0; }
        
        /* Layout specific */
        .header-table td { height: 60px; vertical-align: middle; }
        .info-table td { border-bottom: none; border-top: none; padding: 2px 4px; }
        .info-table tr:first-child td { border-top: 1px solid #000; }
        .info-table tr:last-child td { border-bottom: 1px solid #000; }
        
        .split-table { border: none; }
        .split-table td { border: none; padding: 0; }
        
        .content-table th { background-color: #f9f9f9; text-align: center; }
        
        .signature-table td { height: 100px; vertical-align: top; width: 33.33%; }
        .signature-logo { height: 50px; margin-top: 10px; }
        
        /* Documentation */
        .page-break { page-break-before: always; }
        .gallery-container { width: 100%; }
        .gallery-item { width: 48%; display: inline-block; margin-bottom: 15px; text-align: center; }
        .gallery-img { width: 100%; height: 200px; object-fit: cover; border: 1px solid #000; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td width="20%" class="text-center">
                @if($dailyReport->project->client_logo && file_exists(storage_path('app/public/'.$dailyReport->project->client_logo)))
                    <img src="{{ storage_path('app/public/'.$dailyReport->project->client_logo) }}" style="max-height: 50px; max-width: 100%;">
                @else
                    <span style="color:#999; font-style:italic;">Logo Klien</span>
                @endif
            </td>
            <td width="60%" class="text-center">
                <h2 style="margin:0; font-size: 16px;">DAILY REPORT</h2>
            </td>
            <td width="20%" class="text-center">
                @if(file_exists(public_path('logo11.png')))
                    <img src="{{ public_path('logo11.png') }}" style="max-height: 50px; max-width: 100%;">
                @else
                    <span style="color:#999; font-style:italic;">PT BIO PILAR</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- PROJECT INFO -->
    <table>
        <tr>
            <td colspan="4" class="text-bold bg-gray" style="font-size: 12px; font-style: italic;">
                {{ $dailyReport->project->project_name }}
            </td>
        </tr>
        <tr>
            <td width="15%" class="no-border" style="border-left:1px solid #000!important;">Nomor</td>
            <td width="35%" class="no-border" style="border-right:1px solid #000!important;">: {{ $dailyReport->report_number }}</td>
            <td width="15%" class="no-border">Bidang Pekerjaan</td>
            <td width="35%" class="no-border" style="border-right:1px solid #000!important;">: {{ $dailyReport->project->field_of_work ?? '-' }}</td>
        </tr>
        <tr>
            <td class="no-border" style="border-left:1px solid #000!important;">Tanggal</td>
            <td class="no-border" style="border-right:1px solid #000!important;">: {{ $dailyReport->report_date->format('d F Y') }}</td>
            <td class="no-border">Paket Pekerjaan</td>
            <td class="no-border" style="border-right:1px solid #000!important;">: {{ $dailyReport->project->work_package ?? '-' }}</td>
        </tr>
        <tr>
            <td class="no-border" style="border-left:1px solid #000!important; border-bottom:1px solid #000!important;">Lampiran</td>
            <td class="no-border" style="border-right:1px solid #000!important; border-bottom:1px solid #000!important;">: {{ $dailyReport->attachment ?? 'documentasi' }}</td>
            <td class="no-border">Pelaksana</td>
            <td class="no-border" style="border-right:1px solid #000!important;">: {{ $dailyReport->project->executor_name ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2" class="no-border" style="border-left:1px solid #000!important; border-bottom:1px solid #000!important; border-right:1px solid #000!important;"></td>
            <td class="no-border" style="border-bottom:1px solid #000!important;">No. Kontrak</td>
            <td class="no-border" style="border-right:1px solid #000!important; border-bottom:1px solid #000!important;">: {{ $dailyReport->project->contract_number ?? '-' }}</td>
        </tr>
    </table>

    @php
        $manpower = $dailyReport->manpower;
        $materials = $dailyReport->materials;
        $tools = $dailyReport->tools;

        $mp_count = max(1, $manpower->count());
        $mat_count = max(1, $materials->count());
        $tool_count = max(1, $tools->count());

        $right_total = $mat_count + 1 + $tool_count;
        $total_rows = max($mp_count, $right_total);
    @endphp
    
    <table class="content-table" style="border-top:none;">
        <tr>
            <th width="30%">Man Power</th>
            <th width="10%">Jumlah</th>
            <th width="30%">Supply Material</th>
            <th width="15%">Volume</th>
            <th width="15%">Status</th>
        </tr>
        
        @for($i = 0; $i < $total_rows; $i++)
            <tr>
                {{-- LEFT SIDE --}}
                @if($i < $mp_count)
                    <td>{{ isset($manpower[$i]) ? $manpower[$i]->position : '-' }}</td>
                    <td class="text-center">{{ isset($manpower[$i]) ? $manpower[$i]->quantity : '-' }}</td>
                @elseif($i == $mp_count)
                    <td rowspan="{{ $total_rows - $mp_count }}"></td>
                    <td rowspan="{{ $total_rows - $mp_count }}"></td>
                @endif
                
                {{-- RIGHT SIDE --}}
                @if($i < $mat_count)
                    <td>{{ isset($materials[$i]) ? $materials[$i]->material_name : '-' }}</td>
                    <td class="text-center">{{ isset($materials[$i]) ? $materials[$i]->volume : '-' }}</td>
                    <td class="text-center">{{ isset($materials[$i]) ? ($materials[$i]->status ?? 'on site') : '-' }}</td>
                @elseif($i == $mat_count)
                    <th>Alat kerja</th>
                    <th>Jumlah</th>
                    <th>Unit</th>
                @elseif($i > $mat_count && $i < $right_total)
                    @php $t = $i - $mat_count - 1; @endphp
                    <td>{{ isset($tools[$t]) ? $tools[$t]->tool_name : '-' }}</td>
                    <td class="text-center">{{ isset($tools[$t]) ? $tools[$t]->quantity : '-' }}</td>
                    <td class="text-center">{{ isset($tools[$t]) ? $tools[$t]->unit : '-' }}</td>
                @elseif($i == $right_total)
                    <td rowspan="{{ $total_rows - $right_total }}"></td>
                    <td rowspan="{{ $total_rows - $right_total }}"></td>
                    <td rowspan="{{ $total_rows - $right_total }}"></td>
                @endif
            </tr>
        @endfor
    </table>

    <!-- WORK DESCRIPTION -->
    <table>
        <tr><td class="bg-gray text-bold" style="border-top:none;">Pekerjaan-pekerjaan yang dilaksanakan</td></tr>
        <tr>
            <td style="height: 120px;">
                {!! nl2br(e($dailyReport->work_description)) !!}
            </td>
        </tr>
    </table>

    <!-- EVALUATION -->
    <table>
        <tr><td class="bg-gray text-bold" style="border-top:none;">Catatan dan Evaluasi</td></tr>
        <tr>
            <td style="height: 100px;">
                {!! nl2br(e($dailyReport->evaluation_notes)) !!}
            </td>
        </tr>
    </table>

    <!-- SIGNATURES -->
    <table class="signature-table" style="border-top:none; border-bottom:2px solid #000;">
        <tr>
            <td style="border-top:none; border-left:none; border-bottom:none;">
                Cuaca<br>
                {{ $dailyReport->weather }}
            </td>
            <td style="border-top:none; border-bottom:none;">
                User,<br>
                {{ $dailyReport->project->client_user_name ?? 'RS Kurnia - Cilegon' }}<br>
                @if($dailyReport->project->client_logo && file_exists(storage_path('app/public/'.$dailyReport->project->client_logo)))
                    <img src="{{ storage_path('app/public/'.$dailyReport->project->client_logo) }}" class="signature-logo">
                @endif
            </td>
            <td style="border-top:none; border-right:none; border-bottom:none;">
                Pelaksana,<br>
                <br><br><br><br>
                Project Manager
            </td>
        </tr>
    </table>

    <!-- DOCUMENTATION PAGE -->
    @if($dailyReport->documentations->count() > 0)
    <div class="page-break">
        <h3 class="text-center" style="margin-top: 30px; margin-bottom: 20px;">DOCUMENTATION</h3>
        
        <div class="gallery-container">
            @foreach($dailyReport->documentations as $doc)
            <div class="gallery-item">
                @if(file_exists(storage_path('app/public/'.$doc->photo)))
                    <img src="{{ storage_path('app/public/'.$doc->photo) }}" class="gallery-img">
                @endif
                @if($doc->caption)
                    <div style="font-size: 10px; margin-top: 5px;">{{ $doc->caption }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</body>
</html>
