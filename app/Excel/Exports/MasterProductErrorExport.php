<?php

namespace App\Excel\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterProductErrorExport implements FromArray, WithHeadings, WithStyles
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function array(): array
    {
        return $this->errors;
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Product Type',
            'Engineering Category',
            'Unit',
            'Description',
            'Error Description'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FFE74A3B']]],
        ];
    }
}
