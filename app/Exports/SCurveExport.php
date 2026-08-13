<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SCurveExport implements WithMultipleSheets
{
    use Exportable;

    protected $sCurve;
    protected $tree;
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
        $tree, 
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
        $this->tree = $tree;
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

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: SCurve Report
        $sheets[] = new \App\Exports\Sheets\SCurveReportSheet(
            $this->sCurve, 
            $this->totalWeeks,
            $this->weeklyPlans,
            $this->weeklyActuals,
            $this->cumPlans,
            $this->cumActuals,
            $this->deviations,
            $this->lastInputWeek,
            $this->plannedProgress,
            $this->actualProgress,
            $this->currentDeviation,
            $this->projectStatus
        );

        // Sheet 2: WBS Detail
        $sheets[] = new \App\Exports\Sheets\WBSDetailSheet(
            $this->tree,
            $this->totalWeeks,
            $this->cumPlans,
            $this->cumActuals,
            $this->deviations
        );

        // Sheet 3: Weekly Progress
        $sheets[] = new \App\Exports\Sheets\WeeklyProgressSheet(
            $this->totalWeeks,
            $this->weeklyPlans,
            $this->weeklyActuals,
            $this->cumPlans,
            $this->cumActuals,
            $this->deviations
        );

        return $sheets;
    }
}
