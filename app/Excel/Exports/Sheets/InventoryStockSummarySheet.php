<?php

namespace App\Excel\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InventoryStockSummarySheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Group data by engineering_category
        $grouped = collect($this->data)->groupBy(function($stock) {
            return $stock->product->engineering_category ?? 'Uncategorized';
        });

        $summary = $grouped->map(function($items, $category) {
            return (object) [
                'category' => $category,
                'total_products' => $items->unique('product_id')->count(),
                'total_stock' => $items->sum('quantity')
            ];
        })->values(); // reset keys

        // Add a grand total row
        $summary->push((object) [
            'category' => 'GRAND TOTAL',
            'total_products' => $summary->sum('total_products'),
            'total_stock' => $summary->sum('total_stock')
        ]);

        return $summary;
    }

    public function headings(): array
    {
        return [
            'Engineering Category',
            'Total Unique Products',
            'Total Stock'
        ];
    }

    public function map($row): array
    {
        return [
            $row->category,
            $row->total_products,
            (float) $row->total_stock
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E7D32'] // Green header for summary
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                $cellRange = 'A1:' . $lastColumn . $lastRow;

                // Auto Filter
                $sheet->setAutoFilter('A1:' . $lastColumn . '1');

                // Freeze Header
                $sheet->freezePane('A2');

                // Apply borders
                $sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Right align numeric columns
                $sheet->getStyle('B2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Bold the Grand Total row
                $sheet->getStyle('A' . $lastRow . ':C' . $lastRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $lastRow . ':C' . $lastRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
            },
        ];
    }

    public function title(): string
    {
        return 'Stock Summary';
    }
}
