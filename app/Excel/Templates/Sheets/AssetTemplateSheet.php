<?php

namespace App\Excel\Templates\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetTemplateSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'AST-000001',           // Asset Code
                'Laptop Lenovo Thinkpad', // Asset Name
                'Computer',             // Category
                'Lenovo',               // Brand
                'Thinkpad T14',         // Model
                'SN-123456789',         // Serial Number
                'Office',               // Location
                'Engineering',          // Department
                'Budi@example.com',     // Responsible Person
                '15/01/2026',           // Purchase Date
                '01/02/2026',           // Start Depreciation Date
                25000000,               // Acquisition Cost
                5000000,                // Residual Value
                'Straight Line',        // Commercial Method
                48,                     // Commercial Useful Life
                'Straight Line',        // Fiscal Method
                48                      // Fiscal Useful Life
            ]
        ]);
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
        ];
    }

    public function title(): string
    {
        return 'Asset Template';
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row and freeze it
        $sheet->freezePane('A2');
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
