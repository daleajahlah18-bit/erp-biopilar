<?php

namespace App\Http\Controllers\ProjectReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\DailyReportDocumentation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DailyReportController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(DailyReport::class, strtolower('DailyReport'));
    }
    public function index(Request $request)
    {
        $query = DailyReport::with(['project', 'creator'])->latest();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('month')) {
            $query->whereMonth('report_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('report_date', $request->year);
        }
        if ($request->filled('weather')) {
            $query->where('weather', $request->weather);
        }
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $reports = $query->sortable()->paginate(15)->withQueryString();
        $projects = Project::orderBy('project_name')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('project-reports.daily-reports.index', compact('reports', 'projects', 'users'));
    }

    public function create()
    {
        $projects = Project::orderBy('project_name')->get();
        return view('project-reports.daily-reports.form', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'report_date' => 'required|date',
            'weather' => 'required',
            'manpower' => 'required|array|min:1',
            'tools' => 'required|array|min:1',
            'documentations' => 'required|array|min:1|max:20',
            'documentations.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        try {
            DB::beginTransaction();

            $project = Project::findOrFail($request->project_id);
            
            // Validate Project Identity completeness
            $missingFields = [];
            if (!$project->client_logo) $missingFields[] = 'Client Logo';
            if (!$project->field_of_work) $missingFields[] = 'Field of Work';
            if (!$project->work_package) $missingFields[] = 'Work Package';
            if (!$project->client_user_name) $missingFields[] = 'Client / User Name';
            if (!$project->executor_name) $missingFields[] = 'Executor';
            if (!$project->contract_number) $missingFields[] = 'Contract Number';
            if (!$project->project_address) $missingFields[] = 'Project Location (Site)';

            if (count($missingFields) > 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Project Identity belum lengkap: ' . implode(', ', $missingFields) . '. Silakan lengkapi di menu Master Project.');
            }
            
            // Generate Report Number
            $year = date('Y', strtotime($request->report_date));
            $lastReport = DailyReport::whereYear('report_date', $year)->orderBy('id', 'desc')->first();
            $sequence = $lastReport ? intval(substr($lastReport->report_number, -4)) + 1 : 1;
            $reportNumber = 'DR-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $report = DailyReport::create([
                'project_id' => $project->id,
                'report_number' => $reportNumber,
                'report_date' => $request->report_date,
                'weather' => $request->weather,
                'work_description' => $request->work_description,
                'evaluation_notes' => $request->evaluation_notes,
                'created_by' => auth()->id(),
            ]);

            // Save Manpower
            if ($request->has('manpower')) {
                foreach ($request->manpower as $mp) {
                    if (!empty($mp['position']) && !empty($mp['quantity'])) {
                        $report->manpower()->create($mp);
                    }
                }
            }

            // Save Materials
            if ($request->has('materials')) {
                foreach ($request->materials as $mat) {
                    if (!empty($mat['material_name'])) {
                        $report->materials()->create($mat);
                    }
                }
            }

            // Save Tools
            if ($request->has('tools')) {
                foreach ($request->tools as $tool) {
                    if (!empty($tool['tool_name'])) {
                        $report->tools()->create($tool);
                    }
                }
            }

            // Save Documentations with basic compression
            if ($request->hasFile('documentations')) {
                foreach ($request->file('documentations') as $index => $file) {
                    $filename = Str::uuid() . '.jpg';
                    $path = storage_path('app/public/daily_reports/' . $report->id);
                    if (!file_exists($path)) {
                        mkdir($path, 0755, true);
                    }
                    
                    $this->compressImage($file->getPathname(), $path . '/' . $filename, 60);
                    
                    $caption = $request->input("captions.{$index}") ?? null;
                    
                    $report->documentations()->create([
                        'photo' => 'daily_reports/' . $report->id . '/' . $filename,
                        'caption' => $caption
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('daily-reports.index')->with('success', 'Daily Report created successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(DailyReport $dailyReport)
    {
        $dailyReport->load(['project', 'manpower', 'materials', 'tools', 'documentations', 'creator']);
        return view('project-reports.daily-reports.show', compact('dailyReport'));
    }

    public function edit(DailyReport $dailyReport)
    {
        $projects = Project::orderBy('project_name')->get();
        $dailyReport->load(['manpower', 'materials', 'tools', 'documentations']);
        return view('project-reports.daily-reports.form', compact('dailyReport', 'projects'));
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        // Similar to store, but sync relations. For simplicity we can delete old rows and recreate, 
        // except for photos which we handle separately.
        
        $request->validate([
            'project_id' => 'required',
            'report_date' => 'required|date',
            'weather' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $project = Project::findOrFail($request->project_id);
            
            // Validate Project Identity completeness
            $missingFields = [];
            if (!$project->client_logo) $missingFields[] = 'Client Logo';
            if (!$project->field_of_work) $missingFields[] = 'Field of Work';
            if (!$project->work_package) $missingFields[] = 'Work Package';
            if (!$project->client_user_name) $missingFields[] = 'Client / User Name';
            if (!$project->executor_name) $missingFields[] = 'Executor';
            if (!$project->contract_number) $missingFields[] = 'Contract Number';
            if (!$project->project_address) $missingFields[] = 'Project Location (Site)';

            if (count($missingFields) > 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Project Identity belum lengkap: ' . implode(', ', $missingFields) . '. Silakan lengkapi di menu Master Project.');
            }

            $dailyReport->update([
                'project_id' => $request->project_id,
                'report_date' => $request->report_date,
                'weather' => $request->weather,
                'work_description' => $request->work_description,
                'evaluation_notes' => $request->evaluation_notes,
                'updated_by' => auth()->id(),
            ]);

            // Recreate manpower
            $dailyReport->manpower()->delete();
            if ($request->has('manpower')) {
                foreach ($request->manpower as $mp) {
                    if (!empty($mp['position']) && !empty($mp['quantity'])) {
                        $dailyReport->manpower()->create($mp);
                    }
                }
            }

            // Recreate materials
            $dailyReport->materials()->delete();
            if ($request->has('materials')) {
                foreach ($request->materials as $mat) {
                    if (!empty($mat['material_name'])) {
                        $dailyReport->materials()->create($mat);
                    }
                }
            }

            // Recreate tools
            $dailyReport->tools()->delete();
            if ($request->has('tools')) {
                foreach ($request->tools as $tool) {
                    if (!empty($tool['tool_name'])) {
                        $dailyReport->tools()->create($tool);
                    }
                }
            }

            // Handle new photos
            if ($request->hasFile('documentations')) {
                foreach ($request->file('documentations') as $index => $file) {
                    $filename = Str::uuid() . '.jpg';
                    $path = storage_path('app/public/daily_reports/' . $dailyReport->id);
                    if (!file_exists($path)) {
                        mkdir($path, 0755, true);
                    }
                    
                    $this->compressImage($file->getPathname(), $path . '/' . $filename, 60);
                    
                    $caption = $request->input("captions.{$index}") ?? null;
                    
                    $dailyReport->documentations()->create([
                        'photo' => 'daily_reports/' . $dailyReport->id . '/' . $filename,
                        'caption' => $caption
                    ]);
                }
            }

            // Handle deleted photos
            if ($request->has('deleted_photos')) {
                $toDelete = DailyReportDocumentation::whereIn('id', $request->deleted_photos)->get();
                foreach($toDelete as $doc) {
                    @unlink(storage_path('app/public/' . $doc->photo));
                    $doc->delete();
                }
            }

            DB::commit();
            return redirect()->route('daily-reports.index')->with('success', 'Daily Report updated successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(DailyReport $dailyReport)
    {
        $path = storage_path('app/public/daily_reports/' . $dailyReport->id);
        if (file_exists($path)) {
            // Delete folder and all files
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) @unlink($file);
            }
            @rmdir($path);
        }
        $dailyReport->delete();
        return redirect()->route('daily-reports.index')->with('success', 'Daily Report deleted successfully');
    }

    public function exportPdf(DailyReport $dailyReport)
    {
        $dailyReport->load(['project', 'manpower', 'materials', 'tools', 'documentations', 'creator']);
        
        $pdf = Pdf::loadView('project-reports.daily-reports.pdf', compact('dailyReport'))
                  ->setPaper('a4', 'portrait');
                  
        return $pdf->stream($dailyReport->report_number . '.pdf');
    }

    private function compressImage($source, $destination, $quality) {
        $info = getimagesize($source);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } else {
            return false;
        }
        
        imagejpeg($image, $destination, $quality);
        imagedestroy($image);
        return $destination;
    }
}
