<?php

namespace App\Excel\Templates\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RabTemplateSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell
{
    public function collection()
    {
        return collect([
            ['A', 'PRELIMINARY', '', '', ''],
            ['1', 'Mobilization', '', '', ''],
            ['', 'Mobilization Tools & Equipment', '1', 'LS', '5000000'],
            ['', 'Scaffolding', '20', 'Set', '150000'],
            ['', '', '', '', ''],
            ['B', 'CIVIL WORK', '', '', ''],
            ['1', 'Foundation', '', '', ''],
            ['', 'Excavation', '10.5', 'M3', '75000'],
            ['', 'Concrete K-225', '5.2', 'M3', '950000'],
        ]);
    }

    public function headings(): array
    {
        return [
            ['TEMPLATE RENCANA ANGGARAN BIAYA (RAB)'],
            [''],
            ['Format pengisian:'],
            ['1. Baris Section (A, B, I, II) harus memiliki No dan Description, kolom lain dikosongkan.'],
            ['2. Baris Group (1, 2, 3) harus memiliki No dan Description, kolom lain dikosongkan.'],
            ['3. Baris Item harus mengisi Description, Qty, Unit, dan Unit Price (No dikosongkan).'],
            [''],
            [
                'NO',
                'DESCRIPTION OF WORK',
                'QTY',
                'UNIT',
                'UNIT PRICE (Rp)'
            ]
        ];
    }

    public function title(): string
    {
        return 'RAB Template';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 50,
            'C' => 10,
            'D' => 12,
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        // Instructions
        $sheet->mergeCells('A3:E3');
        $sheet->mergeCells('A4:E4');
        $sheet->mergeCells('A5:E5');
        $sheet->mergeCells('A6:E6');
        
        // Headers (Row 8)
        $sheet->getStyle('A8:E8')->getFont()->setBold(true);
        $sheet->getStyle('A8:E8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:E8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE3E6F0');

        return [
            'A8:E8' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}
