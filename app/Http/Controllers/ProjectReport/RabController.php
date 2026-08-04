<?php

namespace App\Http\Controllers\ProjectReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rab;
use App\Models\RabNode;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class RabController extends Controller
{
    public function index()
    {
        $rabs = Rab::with(['project', 'creator'])->sortable()->latest()->paginate(10);
        return view('project_report.rabs.index', compact('rabs'));
    }

    public function create()
    {
        $projects = Project::orderBy('project_name')->get();
        return view('project_report.rabs.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'rab_name' => 'required|string|max:255',
            'tree_data' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $rab = Rab::create([
                'project_id' => $request->project_id,
                'rab_name' => $request->rab_name,
                'status' => 'Draft',
                'created_by' => auth()->id() ?? 1,
            ]);

            $grandTotal = 0;
            if ($request->tree_data) {
                $treeData = json_decode($request->tree_data, true);
                
                $secOrder = 1;
                foreach ($treeData as $secData) {
                    $sectionNode = RabNode::create([
                        'rab_id' => $rab->id,
                        'parent_id' => null,
                        'title' => $secData['title'],
                        'node_type' => 'Section',
                        'sort_order' => $secOrder++
                    ]);

                    $sectionTotal = 0;
                    if (isset($secData['children'])) {
                        $grpOrder = 1;
                        foreach ($secData['children'] as $grpData) {
                            $groupQty = floatval($grpData['qty'] ?? 0);
                            $groupUnitPrice = floatval($grpData['unit_price'] ?? 0);
                            $groupOwnTotal = $groupQty * $groupUnitPrice;

                            $groupNode = RabNode::create([
                                'rab_id' => $rab->id,
                                'parent_id' => $sectionNode->id,
                                'title' => $grpData['title'],
                                'node_type' => 'Group',
                                'specification' => $grpData['specification'] ?? null,
                                'qty' => $groupQty,
                                'unit' => $grpData['unit'] ?? null,
                                'unit_price' => $groupUnitPrice,
                                'sort_order' => $grpOrder++
                            ]);

                            $groupTotal = $groupOwnTotal;
                            if (isset($grpData['children'])) {
                                $itmOrder = 1;
                                foreach ($grpData['children'] as $itmData) {
                                    $qty = floatval($itmData['qty'] ?? 0);
                                    $unitPrice = floatval($itmData['unit_price'] ?? 0);
                                    $totalPrice = $qty * $unitPrice;

                                    RabNode::create([
                                        'rab_id' => $rab->id,
                                        'parent_id' => $groupNode->id,
                                        'title' => $itmData['title'],
                                        'node_type' => 'Item',
                                        'specification' => $itmData['specification'] ?? null,
                                        'qty' => $qty,
                                        'unit' => $itmData['unit'] ?? null,
                                        'unit_price' => $unitPrice,
                                        'total_price' => $totalPrice,
                                        'sort_order' => $itmOrder++
                                    ]);

                                    $groupTotal += $totalPrice;
                                }
                            }
                            
                            $groupNode->update(['total_price' => $groupTotal]);
                            $sectionTotal += $groupTotal;
                        }
                    }
                    
                    $sectionNode->update(['total_price' => $sectionTotal]);
                    $grandTotal += $sectionTotal;
                }
            }

            $rab->update(['total_amount' => $grandTotal]);

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('rabs.index')
                ]);
            }

            return redirect()->route('rabs.index')->with('success', 'RAB created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return back()->with('error', 'Error creating RAB: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Rab $rab)
    {
        $rab->load(['nodes' => function($q) {
            $q->orderBy('sort_order');
        }]);
        
        // Build a hierarchical tree for the view
        $tree = [];
        $sections = $rab->nodes->where('parent_id', null)->where('node_type', 'Section');
        foreach ($sections as $sec) {
            $groups = $rab->nodes->where('parent_id', $sec->id)->where('node_type', 'Group');
            $sec->children_groups = $groups;
            foreach ($groups as $grp) {
                $items = $rab->nodes->where('parent_id', $grp->id)->where('node_type', 'Item');
                $grp->children_items = $items;
            }
            $tree[] = $sec;
        }

        return view('project_report.rabs.show', compact('rab', 'tree'));
    }

    public function edit(Rab $rab)
    {
        $projects = Project::orderBy('project_name')->get();
        $rab->load(['nodes' => function($q) {
            $q->orderBy('sort_order');
        }]);
        
        $treeData = [];
        $sections = $rab->nodes->where('node_type', 'Section');
        
        foreach ($sections as $sec) {
            $secData = [
                'id' => $sec->id,
                'type' => 'Section',
                'title' => $sec->title,
                'children' => []
            ];
            
            $groups = $rab->nodes->where('parent_id', $sec->id)->where('node_type', 'Group');
            foreach ($groups as $grp) {
                $grpData = [
                    'id' => $grp->id,
                    'type' => 'Group',
                    'title' => $grp->title,
                    'specification' => $grp->specification,
                    'qty' => $grp->qty,
                    'unit' => $grp->unit,
                    'unit_price' => $grp->unit_price,
                    'children' => []
                ];
                
                $items = $rab->nodes->where('parent_id', $grp->id)->where('node_type', 'Item');
                foreach ($items as $itm) {
                    $itmData = [
                        'id' => $itm->id,
                        'type' => 'Item',
                        'title' => $itm->title,
                        'specification' => $itm->specification,
                        'qty' => $itm->qty,
                        'unit' => $itm->unit,
                        'unit_price' => $itm->unit_price
                    ];
                    $grpData['children'][] = $itmData;
                }
                
                $secData['children'][] = $grpData;
            }
            
            $treeData[] = $secData;
        }

        return view('project_report.rabs.edit', compact('rab', 'projects', 'treeData'));
    }

    public function update(Request $request, Rab $rab)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'rab_name' => 'required|string|max:255',
            'tree_data' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $rab->update([
                'project_id' => $request->project_id,
                'rab_name' => $request->rab_name,
            ]);

            // Existing node tracking
            $existingNodeIds = $rab->nodes->pluck('id')->toArray();
            $nodesToKeep = [];
            
            $treeData = json_decode($request->tree_data, true);
            $grandTotal = 0;
            
            $secOrder = 1;
            foreach ($treeData as $secData) {
                $secId = strpos($secData['id'], 'node_') === 0 ? null : $secData['id'];
                
                $section = RabNode::updateOrCreate(
                    ['id' => $secId, 'rab_id' => $rab->id],
                    [
                        'parent_id' => null,
                        'node_type' => 'Section',
                        'title' => $secData['title'],
                        'sort_order' => $secOrder
                    ]
                );
                $nodesToKeep[] = $section->id;
                $secOrder++;

                $sectionTotal = 0;
                if (isset($secData['children'])) {
                    $grpOrder = 1;
                    foreach ($secData['children'] as $grpData) {
                        $grpId = strpos($grpData['id'], 'node_') === 0 ? null : $grpData['id'];
                        $groupQty = floatval($grpData['qty'] ?? 0);
                        $groupUnitPrice = floatval($grpData['unit_price'] ?? 0);
                        $groupOwnTotal = $groupQty * $groupUnitPrice;

                        $group = RabNode::updateOrCreate(
                            ['id' => $grpId, 'rab_id' => $rab->id],
                            [
                                'parent_id' => $section->id,
                                'node_type' => 'Group',
                                'title' => $grpData['title'],
                                'specification' => $grpData['specification'] ?? null,
                                'qty' => $groupQty,
                                'unit' => $grpData['unit'] ?? null,
                                'unit_price' => $groupUnitPrice,
                                'sort_order' => $grpOrder
                            ]
                        );
                        $nodesToKeep[] = $group->id;
                        $grpOrder++;

                        $groupTotal = $groupOwnTotal;
                        if (isset($grpData['children'])) {
                            $itmOrder = 1;
                            foreach ($grpData['children'] as $itmData) {
                                $itmId = strpos($itmData['id'], 'node_') === 0 ? null : $itmData['id'];
                                $qty = floatval($itmData['qty'] ?? 0);
                                $unitPrice = floatval($itmData['unit_price'] ?? 0);
                                $totalPrice = $qty * $unitPrice;

                                $item = RabNode::updateOrCreate(
                                    ['id' => $itmId, 'rab_id' => $rab->id],
                                    [
                                        'parent_id' => $group->id,
                                        'node_type' => 'Item',
                                        'title' => $itmData['title'],
                                        'specification' => $itmData['specification'] ?? null,
                                        'qty' => $qty,
                                        'unit' => $itmData['unit'] ?? null,
                                        'unit_price' => $unitPrice,
                                        'total_price' => $totalPrice,
                                        'sort_order' => $itmOrder
                                    ]
                                );
                                $nodesToKeep[] = $item->id;
                                $itmOrder++;
                                $groupTotal += $totalPrice;
                            }
                        }
                        
                        $group->update(['total_price' => $groupTotal]);
                        $sectionTotal += $groupTotal;
                    }
                }
                
                $section->update(['total_price' => $sectionTotal]);
                $grandTotal += $sectionTotal;
            }

            $rab->update(['total_amount' => $grandTotal]);

            // Delete nodes that were removed
            $nodesToDelete = array_diff($existingNodeIds, $nodesToKeep);
            if (count($nodesToDelete) > 0) {
                RabNode::whereIn('id', $nodesToDelete)->delete();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('rabs.index')]);
            }

            return redirect()->route('rabs.index')->with('success', 'RAB updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return back()->with('error', 'Error updating RAB: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Rab $rab)
    {
        try {
            $rab->delete();
            return redirect()->route('rabs.index')->with('success', 'RAB deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete RAB');
        }
    }
}
