<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Progress Pekerjaan - {{ $reportPhase->report_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            margin: 5px 40px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 5px;
            border-bottom: 2px solid #5ab03a;
            padding-bottom: 5px;
            position: relative;
        }
        .header-table::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 100%;
            height: 1px;
            background-color: #0099cc;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-company {
            width: 90px;
        }
        .header-text h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 18pt;
            font-weight: 900;
            font-family: "Arial Black", Arial, sans-serif;
            letter-spacing: 1px;
        }
        .header-text p {
            margin: 0;
            font-size: 7pt;
            color: #333;
            font-weight: bold;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-bottom: 2px;
            margin-top: 5px;
        }
        .report-number {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 10px;
        }
        .content-text {
            text-align: justify;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .info-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        .info-table .col-label {
            width: 35%;
        }
        .info-table .col-colon {
            width: 2%;
            text-align: center;
        }
        .info-table .col-value {
            width: 63%;
        }
        .signer-table {
            width: 100%;
            margin-bottom: 5px;
            margin-top: 5px;
        }
        .signer-table td {
            vertical-align: top;
        }
        .signer-table .signer-col {
            width: 50%;
        }
        .signer-name {
            font-weight: bold;
            margin-left: 20px;
        }
        .signer-position {
            margin-left: 35px;
        }
        .signature-table {
            width: 100%;
            margin-top: 5px;
            page-break-inside: avoid;
        }
        .signature-table th {
            text-align: left;
            font-weight: normal;
            width: 50%;
            font-size: 10pt;
        }
        .signature-table td {
            text-align: left;
            vertical-align: bottom;
            height: 90px;
        }
        .sign-area {
            text-align: left;
            display: inline-block;
            vertical-align: bottom;
        }
        .sign-name {
            font-weight: bold;
            margin-bottom: 1px;
        }
        .sign-position {
            font-size: 10pt;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                <img src="{{ public_path('logo11.png') }}" class="logo-company" alt="PT Bio Pilar Utama">
            </td>
            <td style="width: 75%; text-align: left;" class="header-text">
                <h1>PT. Bio Pilar Utama</h1>
                <p>JL.RAYA SERANG &ndash; JAKARTA KM. 06</p>
                <p>PERUM. PERSADA BANTEN BLOK C7. NO.1</p>
                <p>DESA KEPUREN &ndash; KECAMATAN WALANTAK &ndash; KOTA SERANG &ndash; BANTEN</p>
            </td>
        </tr>
    </table>

    <div class="title">BERITA ACARA PROGRESS PEKERJAAN</div>
    <div class="report-number">No : {{ $reportPhase->report_number }}</div>

    <div class="content-text">
        {!! $reportPhase->opening_paragraph !!}
    </div>

    <table class="info-table">
        <tr>
            <td class="col-label">No PR</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $reportPhase->project->client_po_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">No Surat Perjanjian<br>Pemborongan Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-value"><br>{{ $reportPhase->project->contract_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Tanggal PR</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $reportPhase->project->client_po_date ? $reportPhase->project->client_po_date->locale('id')->isoFormat('D MMMM Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Deskripsi Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $reportPhase->project->project_name }}</td>
        </tr>
    </table>

    <div class="content-text">
        Kami yang bertanda tangan dibawah ini :
    </div>

    <div style="font-weight: bold; margin-bottom: 5px;">Nama :</div>
    <table class="signer-table">
        <tr>
            @for($i = 1; $i <= 4; $i++)
                @if($reportPhase->{'client_sign_name_'.$i} || $i == 1)
                <td class="signer-col">
                    @if($reportPhase->{'client_sign_name_'.$i})
                    <div><strong>{{ $i }})</strong> <span class="signer-name">{{ $reportPhase->{'client_sign_name_'.$i} }}</span></div>
                    <div class="signer-position">{{ $reportPhase->{'client_sign_position_'.$i} }}</div>
                    @else
                    <div><strong>{{ $i }})</strong> __________________</div>
                    @endif
                </td>
                @endif
            @endfor
        </tr>
    </table>
    <div class="content-text">
        Bertindak selaku dan atas nama <strong>{{ $reportPhase->project->client_name }}</strong> sebagai Pemberi Tugas dan selanjutnya disebut sebagai PIHAK PERTAMA.
    </div>

    <div style="font-weight: bold; margin-bottom: 5px; margin-top: 20px;">Nama :</div>
    <table class="signer-table">
        <tr>
            @for($i = 1; $i <= 4; $i++)
                @if($reportPhase->{'company_sign_name_'.$i} || $i == 1)
                <td class="signer-col">
                    @if($reportPhase->{'company_sign_name_'.$i})
                    <div><strong>{{ $i }})</strong> <span class="signer-name">{{ $reportPhase->{'company_sign_name_'.$i} }}</span></div>
                    <div class="signer-position">{{ $reportPhase->{'company_sign_position_'.$i} }}</div>
                    @else
                    <div><strong>{{ $i }})</strong> __________________</div>
                    @endif
                </td>
                @endif
            @endfor
        </tr>
    </table>
    <div class="content-text">
        Bertindak selaku dan atas nama <strong>PT. Bio Pilar Utama</strong> sebagai Penerima Tugas dan Selanjutnya disebut sebagai PIHAK KEDUA.
    </div>

    <div class="content-text" style="margin-top: 20px;">
        {!! $reportPhase->progress_paragraph !!}
    </div>

    <div class="content-text" style="margin-top: 20px;">
        {!! $reportPhase->closing_paragraph !!}
    </div>

    @if(!empty($reportPhase->additional_notes))
    <div class="content-text" style="margin-top: 20px;">
        {!! $reportPhase->additional_notes !!}
    </div>
    @endif

    <div style="margin-top: 20px;">
        @if($reportPhase->document_location)
            {{ $reportPhase->document_location }},
        @endif
        {{ \Carbon\Carbon::parse($reportPhase->document_date)->locale('id')->isoFormat('D MMMM Y') }}
    </div>

    <table class="signature-table">
        <tr>
            <th>PIHAK PERTAMA</th>
            <th>PIHAK KEDUA</th>
        </tr>
        <tr>
            <td style="text-align: left;">
                @for($i = 1; $i <= 4; $i++)
                    @if($reportPhase->{'client_sign_name_'.$i})
                    <div class="sign-area" style="margin-right: 15px;">
                        <div style="height: 80px;"></div>
                        <div class="sign-name">( {{ $reportPhase->{'client_sign_name_'.$i} }} )</div>
                        <div class="sign-position">{{ $reportPhase->{'client_sign_position_'.$i} }}</div>
                    </div>
                    @endif
                @endfor
            </td>
            <td style="text-align: left;">
                @for($i = 1; $i <= 4; $i++)
                    @if($reportPhase->{'company_sign_name_'.$i})
                    <div class="sign-area" style="margin-right: 15px;">
                        <div style="height: 80px;"></div>
                        <div class="sign-name">( {{ $reportPhase->{'company_sign_name_'.$i} }} )</div>
                        <div class="sign-position">{{ $reportPhase->{'company_sign_position_'.$i} }}</div>
                    </div>
                    @endif
                @endfor
            </td>
        </tr>
    </table>

</body>
</html>
