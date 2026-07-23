<?php

namespace App\Excel\Templates\Sheets;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReferenceSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function array(): array
    {
        $units = Unit::pluck('unit_name')->toArray();
        $types = ['Bahan Baku', 'Bahan Jadi', 'Bill of Material'];
        $categories = ['Civil', 'Mechanical', 'Electrical'];

        // Find max length to create rows
        $maxLength = max(count($units), count($types), count($categories));
        $rows = [];

        for ($i = 0; $i < $maxLength; $i++) {
            $rows[] = [
                $types[$i] ?? '',
                $categories[$i] ?? '',
                $units[$i] ?? ''
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Product Type (Allowed)',
            'Engineering Category (Allowed)',
            'Unit (Allowed)'
        ];
    }

    public function title(): string
    {
        return 'Reference';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
