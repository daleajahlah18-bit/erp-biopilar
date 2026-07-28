<?php

namespace App\Excel\Exports;

use App\Models\Asset;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterAssetExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomValueBinder
{
    protected $assets;

    public function __construct($assets)
    {
        $this->assets = $assets;
    }

    public function collection()
    {
        return $this->assets;
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

    public function map($asset): array
    {
        return [
            $asset->asset_code,
            $asset->asset_name,
            $asset->category->category_name ?? '',
            $asset->brand,
            $asset->model,
            $asset->serial_number,
            $asset->location,
            $asset->department,
            $asset->responsible_person,
            $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d/m/Y') : '',
            $asset->start_depreciation_date ? Carbon::parse($asset->start_depreciation_date)->format('d/m/Y') : '',
            $asset->acquisition_cost,
            $asset->residual_value,
            $asset->commercial_method,
            $asset->commercial_useful_life,
            $asset->fiscal_method,
            $asset->fiscal_useful_life,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        // If it's a long number (like a long asset code or SN), force it to be a string
        if (is_numeric($value) && strlen((string)$value) > 10) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
        
        // Freeze first row
        $sheet->freezePane('A2');
        
        // Add AutoFilter to header row
        $sheet->setAutoFilter('A1:Q1');

        return [];
    }
}
