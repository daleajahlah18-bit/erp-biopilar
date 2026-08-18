<?php

namespace App\Http\Controllers\ProjectReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SCurveController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\ProjectSCurve::with('project');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        $sCurves = $query->latest()->paginate(10)->withQueryString();
        $projects = \App\Models\Project::orderBy('project_name')->get();

        return view('reports.s_curves.index', compact('sCurves', 'projects'));
    }
    public function searchProjects(Request $request)
    {
        $start = microtime(true);
        \Illuminate\Support\Facades\Log::info('[PROJECT SEARCH] CONTROLLER START');

        $q = trim($request->input('q', ''));
        
        $query = \App\Models\Project::query()
            ->select(['id', 'contract_number as project_number', 'project_name', 'client_name', 'project_start_date as start_date', 'project_end_date as end_date']);

        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('contract_number', 'like', "%{$q}%")
                    ->orWhere('project_name', 'like', "%{$q}%")
                    ->orWhere('client_name', 'like', "%{$q}%");
            });
        }

        if ($request->boolean('with_scurves')) {
            $query->with(['sCurves' => function($q) {
                $q->select('id', 'project_id', 'name', 'start_date', 'end_date');
            }]);
        }
        
        $projects = $query->orderBy('contract_number')
            ->limit(20)
            ->get();
            
        \Illuminate\Support\Facades\Log::info('[PROJECT SEARCH] QUERY FINISHED', [
            'ms' => round((microtime(true) - $start) * 1000, 2)
        ]);
            
        return response()->json($projects);
    }

    public function create(Request $request)
    {
        $projects = \App\Models\Project::orderBy('project_name')->get();
        $selectedProject = null;
        if ($request->filled('project_id')) {
            $selectedProject = \App\Models\Project::find($request->project_id);
        }
        return view('reports.s_curves.create', compact('projects', 'selectedProject'));
    }

    public function showImport()
    {
        $projects = \App\Models\Project::orderBy('project_name')->get();
        return view('reports.s_curves.import', compact('projects'));
    }

    public function downloadTemplate(\App\Models\Project $project, \App\Services\SCurveExcelTemplateService $templateService)
    {
        $spreadsheet = $templateService->generateTemplate($project);
        
        $fileName = 'S-Curve_Import_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $project->contract_number) . '.xlsx';
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Output stream
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        
        return $response;
    }

    public function analyzeImport(Request $request, \App\Services\SCurveExcelImportService $importService)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        // Duplicate Check
        $exists = \App\Models\ProjectSCurve::where('project_id', $request->project_id)
            ->where('name', $request->name)
            ->where('start_date', $request->start_date)
            ->where('end_date', $request->end_date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'S-Curve dengan kombinasi Project, Nama, dan Periode yang sama sudah ada. Silakan edit S-Curve yang sudah ada atau gunakan nama/periode yang berbeda.');
        }

        try {
            $parsedData = $importService->parse(
                $request->file('excel_file'),
                $request->project_id,
                $request->name,
                $request->start_date,
                $request->end_date
            );

            // Store in cache with a unique token for 30 minutes
            $token = \Illuminate\Support\Str::random(40);
            \Illuminate\Support\Facades\Cache::put('SCurveImport:' . $token, $parsedData, now()->addMinutes(30));

            $project = \App\Models\Project::find($request->project_id);

            return view('reports.s_curves.import_preview', compact('parsedData', 'token', 'project'));
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function confirmImport(Request $request, \App\Services\SCurveExcelImportService $importService)
    {
        $token = $request->input('token');
        $parsedData = \Illuminate\Support\Facades\Cache::get('SCurveImport:' . $token);

        if (!$parsedData) {
            return redirect()->route('s-curves.import')->with('error', 'Sesi import telah kedaluwarsa. Silakan ulangi proses upload.');
        }

        if (!$parsedData['validation']['is_valid']) {
            return redirect()->route('s-curves.import')->with('error', 'Data gagal divalidasi. Tidak dapat melanjutkan import.');
        }

        try {
            $sCurve = $importService->import($parsedData);
            \Illuminate\Support\Facades\Cache::forget('SCurveImport:' . $token);

            activity()
                ->performedOn($sCurve)
                ->causedBy(auth()->user())
                ->log("Imported S-Curve \"{$sCurve->name}\" from Excel");

            return redirect()->route('s-curves.show', $sCurve->id)->with('success', 'S-Curve berhasil diimport dari Excel!');
        } catch (\Exception $e) {
            return redirect()->route('s-curves.import')->with('error', 'Terjadi kesalahan saat menyimpan ke database: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $sCurve = \App\Models\ProjectSCurve::create([
                'project_id' => $request->project_id,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by' => auth()->id()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            activity()
                ->performedOn($sCurve)
                ->causedBy(auth()->user())
                ->log("Created S-Curve \"{$sCurve->name}\"");

            return redirect()->route('s-curves.show', $sCurve->id)->with('success', 'S-Curve created successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create S-Curve: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $sCurve = \App\Models\ProjectSCurve::with(['project', 'items.plans', 'items.actuals'])->findOrFail($id);
        
        $start = \Carbon\Carbon::parse($sCurve->start_date);
        $end = \Carbon\Carbon::parse($sCurve->end_date);
        $totalDays = $start->diffInDays($end) + 1;
        $totalWeeks = ceil($totalDays / 7);
        if ($totalWeeks == 0) $totalWeeks = 1;

        $items = $sCurve->items()->orderBy('sort_order')->get();
        $tree = $this->buildTree($items);
        
        $leafItems = $items->filter(function($item) use ($items) {
            return !$items->contains('parent_id', $item->id);
        });

        $weeklyPlans = [];
        $weeklyActuals = [];
        for ($i = 1; $i <= $totalWeeks; $i++) {
            $weeklyPlans[$i] = 0;
            $weeklyActuals[$i] = 0;
        }

        foreach ($leafItems as $leaf) {
            foreach ($leaf->plans as $plan) {
                if(isset($weeklyPlans[$plan->week_number])) {
                    $weeklyPlans[$plan->week_number] += $plan->planned_percentage;
                }
            }
            foreach ($leaf->actuals as $actual) {
                if(isset($weeklyActuals[$actual->week_number])) {
                    $weeklyActuals[$actual->week_number] += $actual->actual_percentage;
                }
            }
        }

        $cumPlans = [];
        $cumActuals = [];
        $differences = [];
        $currentCumPlan = 0;
        $currentCumActual = 0;
        
        for ($i = 1; $i <= $totalWeeks; $i++) {
            $currentCumPlan += $weeklyPlans[$i];
            $currentCumActual += $weeklyActuals[$i];
            
            $cumPlans[$i] = round($currentCumPlan, 2);
            $cumActuals[$i] = round($currentCumActual, 2);
            $differences[$i] = round($currentCumActual - $currentCumPlan, 2);
        }

        $lastInputWeek = 0;
        foreach ($weeklyActuals as $wk => $val) {
            if ($val > 0) $lastInputWeek = $wk;
        }
        if($lastInputWeek == 0 && count($leafItems) > 0) {
            $maxActual = \App\Models\ProjectSCurveActual::where('s_curve_id', $sCurve->id)->max('week_number');
            if($maxActual) $lastInputWeek = $maxActual;
        }

        $plannedProgress = $lastInputWeek > 0 ? $cumPlans[$lastInputWeek] : 0;
        $actualProgress = $lastInputWeek > 0 ? $cumActuals[$lastInputWeek] : 0;
        $currentDeviation = $lastInputWeek > 0 ? $differences[$lastInputWeek] : 0;
        
        $projectStatus = 'ON SCHEDULE';
        if ($currentDeviation > 0) $projectStatus = 'AHEAD';
        if ($currentDeviation < 0) $projectStatus = 'BEHIND SCHEDULE';

        return view('reports.s_curves.show', compact('sCurve', 'totalWeeks', 'tree', 'items', 'leafItems', 'weeklyPlans', 'weeklyActuals', 'cumPlans', 'cumActuals', 'differences', 'plannedProgress', 'actualProgress', 'currentDeviation', 'projectStatus'));
    }

    private function buildTree($items, $parentId = null) {
        $branch = [];
        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildTree($items, $item->id);
                if ($children) {
                    $item->children_nodes = $children;
                } else {
                    $item->children_nodes = [];
                }
                $branch[] = $item;
            }
        }
        return $branch;
    }

    public function saveWbs(Request $request, $id)
    {
        $sCurve = \App\Models\ProjectSCurve::findOrFail($id);
        $wbsData = json_decode($request->wbs_data, true);

        if (!is_array($wbsData)) {
            return response()->json(['success' => false, 'message' => 'Invalid WBS data format.']);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $existingItemIds = $sCurve->items()->pluck('id')->toArray();
            $processedIds = [];
            
            $idMap = []; // Map frontend ID to database ID

            foreach ($wbsData as $node) {
                $isNew = str_starts_with((string)$node['id'], 'new_') || !is_numeric($node['id']);
                
                $data = [
                    's_curve_id' => $sCurve->id,
                    'work_code' => $node['work_code'] ?? null,
                    'work_name' => $node['work_name'],
                    'weight_percentage' => $node['weight_percentage'] ?? 0,
                    'sort_order' => $node['sort_order'] ?? 0,
                    'parent_id' => null,
                ];

                if ($isNew) {
                    $item = \App\Models\ProjectSCurveItem::create($data);
                    $idMap[$node['id']] = $item->id;
                    $processedIds[] = $item->id;
                } else {
                    $item = \App\Models\ProjectSCurveItem::find($node['id']);
                    if ($item && $item->s_curve_id == $sCurve->id) {
                        $item->update($data);
                        $idMap[$node['id']] = $item->id;
                        $processedIds[] = $item->id;
                    }
                }
            }

            // Second pass: update parent_id
            foreach ($wbsData as $node) {
                if (!empty($node['parent_id'])) {
                    $dbId = $idMap[$node['id']] ?? null;
                    $parentDbId = $idMap[$node['parent_id']] ?? null;

                    if ($dbId && $parentDbId) {
                        \App\Models\ProjectSCurveItem::where('id', $dbId)->update(['parent_id' => $parentDbId]);
                    }
                }
            }

            // Validation: total weight of leaf nodes must not exceed 100
            $leafWeights = \App\Models\ProjectSCurveItem::where('s_curve_id', $sCurve->id)
                ->whereNotIn('id', function($q) {
                    $q->select('parent_id')->from('project_s_curve_items')->whereNotNull('parent_id');
                })->sum('weight_percentage');
                
            if ($leafWeights > 100.01) {
                throw new \Exception("Total Bobot Pekerjaan (Leaf) melebihi 100%. Total saat ini: " . $leafWeights . "%");
            }

            // Delete missing items
            $itemsToDelete = array_diff($existingItemIds, $processedIds);
            if (!empty($itemsToDelete)) {
                \App\Models\ProjectSCurveItem::whereIn('id', $itemsToDelete)->delete();
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true, 'message' => 'WBS saved successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function savePlans(Request $request, $id)
    {
        $sCurve = \App\Models\ProjectSCurve::findOrFail($id);
        $plans = $request->plans; // Array of [item_id => [week => percentage]]

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($plans as $itemId => $weeks) {
                $item = \App\Models\ProjectSCurveItem::find($itemId);
                if (!$item || $item->s_curve_id != $sCurve->id) continue;

                $totalPlan = 0;
                foreach ($weeks as $weekNum => $percentage) {
                    $pct = floatval($percentage);
                    $totalPlan += $pct;
                    
                    if ($pct > 0) {
                        \App\Models\ProjectSCurvePlan::updateOrCreate(
                            ['s_curve_item_id' => $itemId, 'week_number' => $weekNum],
                            ['planned_percentage' => $pct]
                        );
                    } else {
                        \App\Models\ProjectSCurvePlan::where('s_curve_item_id', $itemId)->where('week_number', $weekNum)->delete();
                    }
                }

                // Check against weight
                if (round($totalPlan, 2) > round($item->weight_percentage, 2)) {
                    throw new \Exception("Total Rencana untuk '{$item->work_name}' ({$totalPlan}%) melebihi bobot pekerjaan ({$item->weight_percentage}%).");
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            activity()
                ->performedOn($sCurve)
                ->causedBy(auth()->user())
                ->log("Updated Plan Progress for S-Curve \"{$sCurve->name}\"");

            return response()->json(['success' => true, 'message' => 'Plans saved successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveActuals(Request $request, $id)
    {
        $sCurve = \App\Models\ProjectSCurve::findOrFail($id);
        $actuals = $request->actuals; // Array of [item_id => [week => percentage]]

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($actuals as $itemId => $weeks) {
                $item = \App\Models\ProjectSCurveItem::find($itemId);
                if (!$item || $item->s_curve_id != $sCurve->id) continue;

                $totalActual = 0;
                foreach ($weeks as $weekNum => $percentage) {
                    $pct = floatval($percentage);
                    $totalActual += $pct;
                    
                    if ($pct > 0) {
                        \App\Models\ProjectSCurveActual::updateOrCreate(
                            ['s_curve_id' => $sCurve->id, 's_curve_item_id' => $itemId, 'week_number' => $weekNum],
                            ['actual_percentage' => $pct, 'updated_by' => auth()->id()]
                        );
                    } else {
                        \App\Models\ProjectSCurveActual::where('s_curve_item_id', $itemId)->where('week_number', $weekNum)->delete();
                    }
                }

                // Check against weight
                if (round($totalActual, 2) > round($item->weight_percentage, 2)) {
                    throw new \Exception("Total Aktual untuk '{$item->work_name}' ({$totalActual}%) melebihi bobot pekerjaan ({$item->weight_percentage}%).");
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            activity()
                ->performedOn($sCurve)
                ->causedBy(auth()->user())
                ->log("Updated Actual Progress for S-Curve \"{$sCurve->name}\"");

            return response()->json(['success' => true, 'message' => 'Actuals saved successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $sCurve = \App\Models\ProjectSCurve::findOrFail($id);
            $sCurveName = $sCurve->name;
            $sCurve->delete();

            activity()
                ->causedBy(auth()->user())
                ->log("Deleted S-Curve \"{$sCurveName}\"");

            return redirect()->route('s-curves.index')->with('success', 'S-Curve deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete S-Curve: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request, $id)
    {
        $sCurve = \App\Models\ProjectSCurve::with(['project', 'items.plans', 'items.actuals'])->findOrFail($id);
        $chartImage = $request->input('chart_image'); // Base64 data

        $start = \Carbon\Carbon::parse($sCurve->start_date);
        $end = \Carbon\Carbon::parse($sCurve->end_date);
        $totalDays = $start->diffInDays($end) + 1;
        $totalWeeks = ceil($totalDays / 7);
        if ($totalWeeks == 0) $totalWeeks = 1;

        $items = $sCurve->items()->orderBy('sort_order')->get();
        $tree = $this->buildTree($items);

        $leafItems = $items->filter(function($item) use ($items) {
            return !$items->contains('parent_id', $item->id);
        });

        $weeklyPlans = [];
        $weeklyActuals = [];
        for ($i = 1; $i <= $totalWeeks; $i++) {
            $weeklyPlans[$i] = 0;
            $weeklyActuals[$i] = 0;
        }

        foreach ($leafItems as $leaf) {
            foreach ($leaf->plans as $plan) {
                if(isset($weeklyPlans[$plan->week_number])) {
                    $weeklyPlans[$plan->week_number] += $plan->planned_percentage;
                }
            }
            foreach ($leaf->actuals as $actual) {
                if(isset($weeklyActuals[$actual->week_number])) {
                    $weeklyActuals[$actual->week_number] += $actual->actual_percentage;
                }
            }
        }

        $cumPlans = [];
        $cumActuals = [];
        $differences = [];
        $currentCumPlan = 0;
        $currentCumActual = 0;
        
        for ($i = 1; $i <= $totalWeeks; $i++) {
            $currentCumPlan += $weeklyPlans[$i];
            $currentCumActual += $weeklyActuals[$i];
            
            $cumPlans[$i] = round($currentCumPlan, 2);
            $cumActuals[$i] = round($currentCumActual, 2);
            $differences[$i] = round($currentCumActual - $currentCumPlan, 2);
        }

        $lastInputWeek = 0;
        foreach ($weeklyActuals as $wk => $val) {
            if ($val > 0) $lastInputWeek = $wk;
        }
        if($lastInputWeek == 0 && count($leafItems) > 0) {
            $maxActual = \App\Models\ProjectSCurveActual::where('s_curve_id', $sCurve->id)->max('week_number');
            if($maxActual) $lastInputWeek = $maxActual;
        }

        $plannedProgress = $lastInputWeek > 0 ? $cumPlans[$lastInputWeek] : 0;
        $actualProgress = $lastInputWeek > 0 ? $cumActuals[$lastInputWeek] : 0;
        $currentDeviation = $lastInputWeek > 0 ? $differences[$lastInputWeek] : 0;
        
        $projectStatus = 'ON SCHEDULE';
        if ($currentDeviation > 0) $projectStatus = 'AHEAD';
        if ($currentDeviation < 0) $projectStatus = 'BEHIND SCHEDULE';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.s_curves.pdf', compact(
            'sCurve', 'totalWeeks', 'tree', 'chartImage',
            'lastInputWeek', 'plannedProgress', 'actualProgress', 'currentDeviation', 'projectStatus',
            'cumPlans', 'cumActuals'
        ))->setPaper('a4', 'landscape');
                
        return $pdf->stream('S-Curve-'.$sCurve->name.'.pdf');
    }

    public function exportExcel($id)
    {
        $sCurve = \App\Models\ProjectSCurve::with(['project', 'items.plans', 'items.actuals'])->findOrFail($id);
        
        $start = \Carbon\Carbon::parse($sCurve->start_date);
        $end = \Carbon\Carbon::parse($sCurve->end_date);
        $totalDays = $start->diffInDays($end) + 1;
        $totalWeeks = ceil($totalDays / 7);
        if ($totalWeeks == 0) $totalWeeks = 1;

        $items = $sCurve->items()->orderBy('sort_order')->get();
        $tree = $this->buildTree($items);

        $leafItems = $items->filter(function($item) use ($items) {
            return !$items->contains('parent_id', $item->id);
        });

        $weeklyPlans = [];
        $weeklyActuals = [];
        for ($i = 1; $i <= $totalWeeks; $i++) {
            $weeklyPlans[$i] = 0;
            $weeklyActuals[$i] = 0;
        }

        foreach ($leafItems as $leaf) {
            foreach ($leaf->plans as $plan) {
                if(isset($weeklyPlans[$plan->week_number])) {
                    $weeklyPlans[$plan->week_number] += $plan->planned_percentage;
                }
            }
            foreach ($leaf->actuals as $actual) {
                if(isset($weeklyActuals[$actual->week_number])) {
                    $weeklyActuals[$actual->week_number] += $actual->actual_percentage;
                }
            }
        }

        $cumPlans = [];
        $cumActuals = [];
        $differences = [];
        $currentCumPlan = 0;
        $currentCumActual = 0;
        
        for ($i = 1; $i <= $totalWeeks; $i++) {
            $currentCumPlan += $weeklyPlans[$i];
            $currentCumActual += $weeklyActuals[$i];
            
            $cumPlans[$i] = round($currentCumPlan, 2);
            $cumActuals[$i] = round($currentCumActual, 2);
            $differences[$i] = round($currentCumActual - $currentCumPlan, 2);
        }

        $lastInputWeek = 0;
        foreach ($weeklyActuals as $wk => $val) {
            if ($val > 0) $lastInputWeek = $wk;
        }
        if($lastInputWeek == 0 && count($leafItems) > 0) {
            $maxActual = \App\Models\ProjectSCurveActual::where('s_curve_id', $sCurve->id)->max('week_number');
            if($maxActual) $lastInputWeek = $maxActual;
        }

        $plannedProgress = $lastInputWeek > 0 ? $cumPlans[$lastInputWeek] : 0;
        $actualProgress = $lastInputWeek > 0 ? $cumActuals[$lastInputWeek] : 0;
        $currentDeviation = $lastInputWeek > 0 ? $differences[$lastInputWeek] : 0;
        
        $projectStatus = 'ON SCHEDULE';
        if ($currentDeviation > 0) $projectStatus = 'AHEAD';
        if ($currentDeviation < 0) $projectStatus = 'BEHIND SCHEDULE';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SCurveExport(
                $sCurve, 
                $tree, 
                $totalWeeks,
                $weeklyPlans,
                $weeklyActuals,
                $cumPlans,
                $cumActuals,
                $differences,
                $lastInputWeek,
                $plannedProgress,
                $actualProgress,
                $currentDeviation,
                $projectStatus
            ), 
            'SCurve_'.preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $sCurve->project->project_name ?? 'Project')).'_'.date('Ymd').'.xlsx'
        );
    }
}
