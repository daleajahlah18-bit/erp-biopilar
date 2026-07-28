<?php

namespace App\Excel\Templates\Sheets;

use App\Models\AssetCategory;
use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetReferenceSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $categories = AssetCategory::pluck('category_name')->toArray();
        $departments = Asset::whereNotNull('department')->distinct()->pluck('department')->toArray();
        $locations = Asset::whereNotNull('location')->distinct()->pluck('location')->toArray();
        $methods = ['Straight Line', 'Double Declining Balance'];

        $maxCount = max(count($categories), count($departments), count($locations), count($methods));
        
        $data = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $data[] = [
                $categories[$i] ?? '',
                $departments[$i] ?? '',
                $locations[$i] ?? '',
                $methods[$i] ?? '',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Available Categories',
            'Existing Departments',
            'Existing Locations',
            'Depreciation Methods',
        ];
    }

    public function title(): string
    {
        return 'Reference';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
