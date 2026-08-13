<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WBSDetailSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $tree;
    protected $totalWeeks;
    protected $cumPlans;
    protected $cumActuals;
    protected $deviations;
    protected $globalCounters = [];
    protected $parentRows = [];
    protected $currentRow = 3; // Starts after headings

    public function __construct(
        $tree,
        $totalWeeks,
        $cumPlans,
        $cumActuals,
        $deviations
    ) {
        $this->tree = $tree;
        $this->totalWeeks = $totalWeeks;
        $this->cumPlans = $cumPlans;
        $this->cumActuals = $cumActuals;
        $this->deviations = $deviations;
        
        $this->globalCounters = ['rowCount' => 0];
    }

    public function array(): array
    {
        $rows = [];
        $this->buildExcelRows($this->tree, 0, $rows);
        
        // Add Summary Rows at the bottom
        $planLabel = new RichText();
        $pr = $planLabel->createTextRun('PLAN CUMULATIVE (%)');
        $pr->getFont()->setBold(true)->setColor(new Color('FF0044CC'));
        
        $actLabel = new RichText();
        $ar = $actLabel->createTextRun('ACTUAL CUMULATIVE (%)');
        $ar->getFont()->setBold(true)->setColor(new Color('FFCC0000'));

        $planCumRow = ['', $planLabel, ''];
        $actCumRow = ['', $actLabel, ''];
        $devRow = ['', 'DEVIATION (%)', ''];
        
        for ($w = 1; $w <= $this->totalWeeks; $w++) {
            $planCumRow[] = number_format($this->cumPlans[$w] ?? 0, 2);
            $actCumRow[] = number_format($this->cumActuals[$w] ?? 0, 2);
            
            $devVal = $this->deviations[$w] ?? 0;
            if ($devVal < 0) {
                $devRt = new RichText();
                $r = $devRt->createTextRun(number_format($devVal, 2));
                $r->getFont()->setColor(new Color('FFCC0000'));
                $devRow[] = $devRt;
            } elseif ($devVal > 0) {
                $devRt = new RichText();
                $r = $devRt->createTextRun(number_format($devVal, 2));
                $r->getFont()->setColor(new Color('FF008800'));
                $devRow[] = $devRt;
            } else {
                $devRow[] = number_format($devVal, 2);
            }
        }
        
        // Blank row
        $rows[] = array_fill(0, $this->totalWeeks + 3, '');
        $this->currentRow++;
        
        $rows[] = $planCumRow;
        $this->parentRows[] = $this->currentRow; // Bold this row
        $this->currentRow++;
        
        $rows[] = $actCumRow;
        $this->parentRows[] = $this->currentRow;
        $this->currentRow++;
        
        $rows[] = $devRow;
        $this->parentRows[] = $this->currentRow;
        
        return $rows;
    }
    
    protected function buildExcelRows($nodes, $level, &$rows)
    {
        if (!isset($this->globalCounters[$level])) {
            $this->globalCounters[$level] = 0;
        }

        foreach ($nodes as $node) {
            $isParent = count($node->children_nodes) > 0;
            
            // Format number based on level
            if ($level == 0) {
                $number = $this->numberToRoman($this->globalCounters[$level] + 1);
            } elseif ($level == 1) {
                $number = chr(97 + $this->globalCounters[$level]) . '.'; // a., b.
            } else {
                $number = ($this->globalCounters[$level] + 1) . '.'; // 1., 2.
            }
            
            $indent = str_repeat('    ', $level);
            
            $row = [];
            $row[] = $number;
            $row[] = $indent . $node->work_name;
            $row[] = number_format($node->weight_percentage, 2);
            
            if ($isParent) {
                $this->parentRows[] = $this->currentRow;
                // Empty weeks for parent
                for ($w = 1; $w <= $this->totalWeeks; $w++) {
                    $row[] = '';
                }
            } else {
                // Leaf node: RichText for Plan & Actual
                $plans = [];
                $actuals = [];
                foreach ($node->plans as $p) $plans[$p->week_number] = $p->planned_percentage;
                foreach ($node->actuals as $a) $actuals[$a->week_number] = $a->actual_percentage;
                
                for ($w = 1; $w <= $this->totalWeeks; $w++) {
                    $pVal = $plans[$w] ?? null;
                    $aVal = $actuals[$w] ?? null;
                    
                    if ($pVal !== null || $aVal !== null) {
                        $richText = new RichText();
                        
                        if ($pVal !== null) {
                            $planRun = $richText->createTextRun(number_format($pVal, 2) . "%");
                            $planRun->getFont()->setColor(new Color('FF0044CC')); // Blue
                        }
                        
                        if ($aVal !== null) {
                            if ($pVal !== null) {
                                $richText->createText("\n");
                            }
                            $actRun = $richText->createTextRun(number_format($aVal, 2) . "%");
                            $actRun->getFont()->setColor(new Color('FFCC0000')); // Red
                        }
                        
                        $row[] = $richText;
                    } else {
                        $row[] = '';
                    }
                }
            }
            
            $rows[] = $row;
            $this->currentRow++;
            $this->globalCounters[$level]++;
            
            if ($isParent) {
                $this->globalCounters[$level + 1] = 0; // reset child counter
                $this->buildExcelRows($node->children_nodes, $level + 1, $rows);
            }
        }
    }
    
    protected function numberToRoman($num) 
    {
        $n = intval($num);
        $res = '';
        $roman_numerals = array(
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1
        );
        foreach ($roman_numerals as $roman => $number) {
            $matches = intval($n / $number);
            $res .= str_repeat($roman, $matches);
            $n = $n % $number;
        }
        return $res;
    }

    public function headings(): array
    {
        $row1 = ['NO', 'JENIS PEKERJAAN', 'BOBOT (%)'];
        $row2 = ['', '', ''];
        
        for ($w = 1; $w <= $this->totalWeeks; $w++) {
            $row1[] = 'W' . $w;
        }
        
        return [
            $row1
        ];
    }

    public function title(): string
    {
        return 'WBS Detail';
    }
    
    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,
            'B' => 45,
            'C' => 12,
        ];
        
        // Generate column letters for weeks (starting from D)
        for ($w = 1; $w <= $this->totalWeeks; $w++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($w + 3);
            $widths[$colLetter] = 12;
        }
        
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        // General styling for the whole sheet
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $range = 'A1:' . $highestCol . $highestRow;
        
        $sheet->getStyle($range)->getAlignment()->setWrapText(true);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        // Style headers
        $sheet->getStyle('A1:' . $highestCol . '1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK],
            ],
        ]);
        
        // Borders for all cells
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);
        
        // Alignment for specific columns
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D2:' . $highestCol . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Bold parent rows
        foreach ($this->parentRows as $rowNum) {
            $sheet->getStyle('A' . $rowNum . ':' . $highestCol . $rowNum)->getFont()->setBold(true);
        }
        
        // Freeze Panes
        $sheet->freezePane('D2');
        
        return [];
    }
}
