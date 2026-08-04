<?php

namespace App\Excel\Templates\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Rab;

class RabExportSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell
{
    protected $rab;
    protected $currentRow = 7;
    protected $styles = [];

    public function __construct(Rab $rab)
    {
        $this->rab = $rab;
    }

    public function collection()
    {
        $rows = collect();
        $sections = $this->rab->nodes->where('parent_id', null)->where('node_type', 'Section');
        
        $secIndex = 1;
        foreach ($sections as $section) {
            $secLetter = $secIndex++ . '.';
            $rows->push([
                $secLetter,
                $section->title,
                '',
                '',
                '',
                $section->total_price
            ]);
            $this->styles[$this->currentRow] = 'Section';
            $this->currentRow++;
            
            $groups = $this->rab->nodes->where('parent_id', $section->id)->where('node_type', 'Group');
            $grpNumber = 1;
            foreach ($groups as $group) {
                $rows->push([
                    $grpNumber,
                    $group->title . ($group->specification ? "\n" . $group->specification : ''),
                    $group->qty > 0 ? (float)$group->qty : '',
                    $group->unit,
                    $group->qty > 0 ? (float)$group->unit_price : '',
                    (float)$group->total_price
                ]);
                $this->styles[$this->currentRow] = 'Group';
                $this->currentRow++;

                $items = $this->rab->nodes->where('parent_id', $group->id)->where('node_type', 'Item');
                $itmNumber = 1;
                foreach ($items as $item) {
                    $description = $item->title;
                    if ($item->specification) {
                        $description .= "\n" . $item->specification;
                    }
                    $rows->push([
                        $itmNumber++,
                        $description,
                        $item->qty,
                        $item->unit,
                        $item->unit_price,
                        $item->total_price
                    ]);
                    $this->styles[$this->currentRow] = 'Item';
                    $this->currentRow++;
                }
                $grpNumber++;
            }
        }

        // Grand Total Row
        $rows->push([
            '',
            'GRAND TOTAL',
            '',
            '',
            '',
            $this->rab->total_amount
        ]);
        $this->styles[$this->currentRow] = 'GrandTotal';
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            ['RENCANA ANGGARAN BIAYA (RAB)'],
            ['Project:', $this->rab->project->project_name ?? '-'],
            ['RAB Name:', $this->rab->rab_name],
            ['Date:', $this->rab->created_at->format('d F Y')],
            [''],
            [
                'NO',
                'DESCRIPTION OF WORK',
                'QTY',
                'UNIT',
                'UNIT PRICE (Rp)',
                'TOTAL PRICE (Rp)'
            ]
        ];
    }

    public function title(): string
    {
        return substr($this->rab->rab_name, 0, 31); // Excel limits tab names to 31 chars
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
            'F' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title & Header Info
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        
        // Table Headers (Row 6)
        $sheet->getStyle('A6:F6')->getFont()->setBold(true);
        $sheet->getStyle('A6:F6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE3E6F0');

        $styleArray = [
            'A6:F' . $this->currentRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ]
            ],
        ];

        // Apply styles to individual rows based on their type
        foreach ($this->styles as $row => $type) {
            if ($type == 'Section') {
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2E3E5'); // Secondary
                $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            } elseif ($type == 'Group') {
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
                $sheet->getStyle("B{$row}")->getAlignment()->setIndent(2);
                $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            } elseif ($type == 'Item') {
                $sheet->getStyle("B{$row}")->getAlignment()->setIndent(4);
                $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            } elseif ($type == 'GrandTotal') {
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFCFE2FF'); // Primary light
                $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }
        }

        return $styleArray;
    }
}
