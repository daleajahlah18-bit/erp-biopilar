<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->report_number }}</title>
    <style>
        @page {
            margin: 50px 40px 100px 40px; /* Top, Right, Bottom, Left */
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
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
            border: none;
            padding: 0;
        }
        .logo-company {
            width: 120px;
        }
        .header-text h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 22pt;
            font-weight: 900;
            font-family: "Arial Black", Arial, sans-serif;
            letter-spacing: 1px;
        }
        .header-text p {
            margin: 0;
            font-size: 8pt;
            color: #333;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }
        
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            font-size: 11pt;
            font-family: 'Times New Roman', Times, serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .img-wrapper {
            text-align: center;
            width: 100%;
            padding: 5px 0;
        }
        .img-wrapper img {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        
        .remark-text {
            font-size: 10pt;
            margin-top: 5px;
        }
        
        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 10px;
            margin-top: 20px;
        }
        .client-name {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .opening-desc {
            margin-bottom: 15px;
            text-align: justify;
        }
        .closing-desc {
            margin-top: 20px;
            text-align: justify;
        }
        .signature {
            margin-top: 40px;
            width: 300px;
        }
        
        /* Vertical alignment for table cells */
        td { vertical-align: middle; }
        td.align-top { vertical-align: top; }
        
        /* specific widths */
        .col-no-group { width: 5%; text-align: center; }
        .col-no-item { width: 5%; text-align: center; }
        .col-desc { width: 50%; }
        .col-qty { width: 15%; text-align: center; }
        .col-lampiran { width: 25%; text-align: center; }
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
                <p>DESA KEPUREN &ndash; KECAMATAN WALANTAKA &ndash; KOTA SERANG &ndash; BANTEN</p>
            </td>
        </tr>
    </table>

    <main>
        <div class="title-doc">LAPORAN HASIL KUNJUNGAN</div>
        <div class="client-name">
            {{ $report->client_name }}
        </div>

        <div class="opening-desc">
            <p>{{ $report->opening_description }}</p>
        </div>

        <table>
            @if(count($flatRows) > 0)
                <thead>
                    <tr>
                        <th class="col-no-group" style="padding: 0; border: none; height: 0;"></th>
                        <th class="col-no-item" style="padding: 0; border: none; height: 0;"></th>
                        <th class="col-desc" style="padding: 0; border: none; height: 0;"></th>
                        <th class="col-qty" style="padding: 0; border: none; height: 0;"></th>
                        <th class="col-lampiran" style="padding: 0; border: none; height: 0;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($flatRows as $row)
                        @if($row['type'] == 'category')
                            <tr>
                                <td colspan="4" class="font-bold text-left" style="text-transform: uppercase;">
                                    {{ $row['title'] }}
                                </td>
                                <td class="font-bold text-center">Lampiran</td>
                            </tr>
                        @elseif($row['type'] == 'group')
                            <tr>
                                <td class="text-center align-top">{{ $row['no'] }}</td>
                                <td colspan="3" class="text-left align-top">{{ $row['title'] }}</td>
                                <td></td>
                            </tr>
                        @elseif($row['type'] == 'item')
                            <tr>
                                <td></td>
                                <td class="text-center">{{ $row['no'] }}</td>
                                <td>
                                    {{ $row['title'] }}
                                    @if($row['remark'])
                                        <div class="remark-text">{{ $row['remark'] }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $row['qty'] }}</td>
                                <td class="text-center">
                                    @if(isset($row['attachments']) && $row['attachments']->count() > 0)
                                        <div class="img-wrapper">
                                            @foreach($row['attachments'] as $attachment)
                                                <img src="{{ storage_path('app/public/' . $attachment->file_path) }}" alt="Lampiran">
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            @endif
        </table>

        @if($report->closing_description)
            <div class="closing-desc">
                <p>{!! nl2br(e($report->closing_description)) !!}</p>
            </div>
        @endif

        <div class="signature">
            <p>Terima kasih,</p>
            <br><br><br>
            <p>({{ $report->surveyor }})<br>PT. Bio Pilar Utama</p>
        </div>
    </main>
</body>
</html>
