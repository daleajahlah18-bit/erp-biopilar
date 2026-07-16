<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Reports\ProjectReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectReportController extends Controller
{
    protected $reportService;

    public function __construct(ProjectReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $projects = Project::orderBy('project_name')->get();
        return view('reports.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $projectInfo = $this->reportService->getProjectSummary($project->id);
        $salesHistory = $this->reportService->getSalesHistory($project->id);
        $purchasedMaterials = $this->reportService->getPurchasedMaterials($project->id);
        $materialUsage = $this->reportService->getMaterialUsage($project->id);

        $serviceUsage = $this->reportService->getServiceUsage($project->id);
        $financialSummary = $this->reportService->calculateProjectMargin($projectInfo);

        return view('reports.projects.show', compact(
            'project', 
            'projectInfo', 
            'salesHistory', 
            'purchasedMaterials',
            'materialUsage', 
            'serviceUsage',
            'financialSummary'
        ));
    }

    public function printPdf(Project $project)
    {
        $projectInfo = $this->reportService->getProjectSummary($project->id);
        $salesHistory = $this->reportService->getSalesHistory($project->id);
        $purchasedMaterials = $this->reportService->getPurchasedMaterials($project->id);
        $materialUsage = $this->reportService->getMaterialUsage($project->id);

        $serviceUsage = $this->reportService->getServiceUsage($project->id);
        $financialSummary = $this->reportService->calculateProjectMargin($projectInfo);

        $pdf = Pdf::loadView('reports.projects.pdf', compact(
            'project', 
            'projectInfo', 
            'salesHistory',
            'purchasedMaterials',
            'materialUsage', 
            'serviceUsage',
            'financialSummary'
        ));
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download("Project_Report_{$project->id}.pdf");
    }
}
