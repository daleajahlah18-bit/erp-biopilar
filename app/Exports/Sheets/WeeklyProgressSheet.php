<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WeeklyProgressSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $totalWeeks;
    protected $weeklyPlans;
    protected $weeklyActuals;
    protected $cumPlans;
    protected $cumActuals;
    protected $deviations;

    public function __construct(
        $totalWeeks,
        $weeklyPlans,
        $weeklyActuals,
        $cumPlans,
        $cumActuals,
        $deviations
    ) {
        $this->totalWeeks = $totalWeeks;
        $this->weeklyPlans = $weeklyPlans;
        $this->weeklyActuals = $weeklyActuals;
        $this->cumPlans = $cumPlans;
        $this->cumActuals = $cumActuals;
        $this->deviations = $deviations;
    }

    public function collection()
    {
        $data = [];
        for ($w = 1; $w <= $this->totalWeeks; $w++) {
            $data[] = [
                'Week ' . $w,
                (float) number_format($this->weeklyPlans[$w] ?? 0, 2, '.', ''),
                (float) number_format($this->weeklyActuals[$w] ?? 0, 2, '.', ''),
                (float) number_format($this->cumPlans[$w] ?? 0, 2, '.', ''),
                (float) number_format($this->cumActuals[$w] ?? 0, 2, '.', ''),
                (float) number_format($this->deviations[$w] ?? 0, 2, '.', ''),
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Week',
            'Plan Weekly (%)',
            'Actual Weekly (%)',
            'Plan Cumulative (%)',
            'Actual Cumulative (%)',
            'Deviation (%)',
        ];
    }

    public function title(): string
    {
        return 'Weekly Progress';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
