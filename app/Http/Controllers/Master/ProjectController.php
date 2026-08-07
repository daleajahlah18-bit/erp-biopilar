<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Requests\Master\ProjectRequest;
use App\Services\Master\ProjectService;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Reports\ProjectReportService;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->authorizeResource(Project::class, strtolower('Project'));
        $this->projectService = $projectService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $query = Project::query();

        // Validate filters
        $validStatuses = ['Draft', 'On Going', 'Completed', 'Cancelled'];
        
        if ($request->filled('status') && in_array($request->status, $validStatuses)) {
            $query->where('project_status', $request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            if (strtotime($request->date_from) > strtotime($request->date_to)) {
                return back()->with('error', 'Filter tanggal tidak valid: Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('project_start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('project_start_date', '<=', $request->date_to);
        }

        $projects = $query->latest()->sortable()->paginate(10)->withQueryString();
        return view('master.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('master.projects.create');
    }

    public function store(ProjectRequest $request, \App\Services\ProjectCostingService $costingService)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            if ($request->hasFile('client_logo')) {
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('client_logos')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('client_logos');
                }
                
                $file = $request->file('client_logo');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->extension();
                \Intervention\Image\ImageManagerStatic::make($file)->encode($file->extension(), 90)->save(storage_path('app/public/client_logos/' . $filename));
                $data['client_logo'] = 'client_logos/' . $filename;
            }
            
            $project = Project::create($data);

            if (!empty($data['payment_terms'])) {
                foreach ($data['payment_terms'] as $term) {
                    $project->projectPaymentTerms()->create($term);
                }
            }

            $costingService->recalculateProject($project);

            DB::commit();
            return redirect()->route('master.projects.index')->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create project: ' . $e->getMessage());
        }
    }

    public function show(Project $project)
    {
        // Calculate dynamic values or pass directly
        return view('master.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('master.projects.edit', compact('project'));
    }

    public function update(ProjectRequest $request, Project $project, \App\Services\ProjectCostingService $costingService)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            if ($request->hasFile('client_logo')) {
                if ($project->client_logo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($project->client_logo);
                }
                
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('client_logos')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('client_logos');
                }
                
                $file = $request->file('client_logo');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->extension();
                \Intervention\Image\ImageManagerStatic::make($file)->encode($file->extension(), 90)->save(storage_path('app/public/client_logos/' . $filename));
                $data['client_logo'] = 'client_logos/' . $filename;
            }
            
            $project->update($data);

            // Re-sync payment terms
            $project->projectPaymentTerms()->delete();
            if (!empty($data['payment_terms'])) {
                foreach ($data['payment_terms'] as $term) {
                    $project->projectPaymentTerms()->create($term);
                }
            }

            $costingService->recalculateProject($project);

            DB::commit();
            return redirect()->route('master.projects.index')->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update project: ' . $e->getMessage());
        }
    }

    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return redirect()->route('master.projects.index')->with('success', 'Project deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete project: ' . $e->getMessage());
        }
    }

    public function pdf(Project $project, ProjectReportService $reportService)
    {
        $project->load('projectPaymentTerms');

        activity('export')
            ->performedOn($project)
            ->causedBy(auth()->user())
            ->log("Generated Project Information PDF\nProject: {$project->project_name}\nProject Code: {$project->id}\nGenerated By: " . (auth()->user()->name ?? 'System'));

        $pdf = Pdf::loadView('master.projects.pdf', compact('project'))
                  ->setPaper('a4', 'portrait');

        $fileName = 'Project_Information_' . Str::slug($project->project_name, '_') . '.pdf';

        return $pdf->stream($fileName);
    }
}