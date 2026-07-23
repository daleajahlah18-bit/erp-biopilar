<?php

namespace App\Excel\Templates\Sheets;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['PRD-0001', 'PVC Pipe 4 Inch', 'Bahan Baku', 'Civil', 'PCS', 'PVC Pipe']
        ];
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Product Type',
            'Engineering Category',
            'Unit',
            'Description'
        ];
    }

    public function title(): string
    {
        return 'Products';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
