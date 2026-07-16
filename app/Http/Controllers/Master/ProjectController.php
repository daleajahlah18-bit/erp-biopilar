<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Requests\Master\ProjectRequest;
use App\Services\Master\ProjectService;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->authorizeResource(Project::class, strtolower('Project'));
        $this->projectService = $projectService;
    }

    public function index()
    {
        $projects = Project::latest()->sortable()->paginate(10);
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
}