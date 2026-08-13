<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SCurveReportSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $sCurve;
    protected $totalWeeks;
    protected $weeklyPlans;
    protected $weeklyActuals;
    protected $cumPlans;
    protected $cumActuals;
    protected $deviations;
    protected $lastInputWeek;
    protected $plannedProgress;
    protected $actualProgress;
    protected $currentDeviation;
    protected $projectStatus;

    public function __construct(
        $sCurve, 
        $totalWeeks,
        $weeklyPlans,
        $weeklyActuals,
        $cumPlans,
        $cumActuals,
        $deviations,
        $lastInputWeek,
        $plannedProgress,
        $actualProgress,
        $currentDeviation,
        $projectStatus
    ) {
        $this->sCurve = $sCurve;
        $this->totalWeeks = $totalWeeks;
        $this->weeklyPlans = $weeklyPlans;
        $this->weeklyActuals = $weeklyActuals;
        $this->cumPlans = $cumPlans;
        $this->cumActuals = $cumActuals;
        $this->deviations = $deviations;
        $this->lastInputWeek = $lastInputWeek;
        $this->plannedProgress = $plannedProgress;
        $this->actualProgress = $actualProgress;
        $this->currentDeviation = $currentDeviation;
        $this->projectStatus = $projectStatus;
    }

    public function view(): View
    {
        return view('reports.s_curves.excel_report', [
            'sCurve' => $this->sCurve,
            'totalWeeks' => $this->totalWeeks,
            'weeklyPlans' => $this->weeklyPlans,
            'weeklyActuals' => $this->weeklyActuals,
            'cumPlans' => $this->cumPlans,
            'cumActuals' => $this->cumActuals,
            'deviations' => $this->deviations,
            'lastInputWeek' => $this->lastInputWeek,
            'plannedProgress' => $this->plannedProgress,
            'actualProgress' => $this->actualProgress,
            'currentDeviation' => $this->currentDeviation,
            'projectStatus' => $this->projectStatus,
        ]);
    }

    public function title(): string
    {
        return 'S-Curve Report';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        return [];
    }
}
