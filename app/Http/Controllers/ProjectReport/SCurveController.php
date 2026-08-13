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
        $q = $request->input('q');
        
        $projects = \App\Models\Project::with('sCurves')
            ->where(function($query) use ($q) {
                $query->where('project_number', 'like', "%{$q}%")
                      ->orWhere('project_name', 'like', "%{$q}%")
                      ->orWhere('client_name', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get();
            
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
            $sCurve->delete();
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
