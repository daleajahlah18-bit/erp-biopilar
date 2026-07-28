<?php

namespace App\Excel\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterAssetErrorExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
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
            'Asset Code',
            'Asset Name',
            'Category',
            'Brand',
            'Model',
            'Serial Number',
            'Location',
            'Department',
            'Responsible Person',
            'Purchase Date',
            'Start Depreciation Date',
            'Acquisition Cost',
            'Residual Value',
            'Commercial Method',
            'Commercial Useful Life',
            'Fiscal Method',
            'Fiscal Useful Life',
            'Error Message',
            'Suggested Fix'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        // Color the error message columns red, and suggested fix green/blue
        $sheet->getStyle('R1:R' . (count($this->errors) + 1))->getFont()->getColor()->setARGB('FFFF0000');
        $sheet->getStyle('S1:S' . (count($this->errors) + 1))->getFont()->getColor()->setARGB('FF0070C0');

        return [];
    }
}
