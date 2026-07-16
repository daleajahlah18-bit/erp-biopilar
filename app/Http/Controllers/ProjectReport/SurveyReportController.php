<?php

namespace App\Http\Controllers\ProjectReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SurveyReport;
use App\Models\SurveyReportNode;
use App\Models\SurveyReportAttachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SurveyReportController extends Controller
{
    public function index()
    {
        $reports = SurveyReport::with('creator')->sortable()->latest()->sortable()->paginate(10);
        return view('project_report.survey_reports.index', compact('reports'));
    }

    public function create()
    {
        $defaultOpening = "Berdasarkan hasil kunjungan lapangan yang telah dilakukan pada tanggal ... terhadap lokasi tersebut, berikut kami sampaikan hasil survey sebagai berikut.";
        $defaultClosing = "Demikian laporan hasil kunjungan dari kami, apabila diperlukan informasi lebih lanjut kami siap membantu.\n\nTerima kasih.";

        return view('project_report.survey_reports.create', compact('defaultOpening', 'defaultClosing'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required',
            'survey_location' => 'required',
            'survey_date' => 'required|date',
            'surveyor' => 'required',
            'opening_description' => 'required',
        ]);

        DB::beginTransaction();
        try {
            // Generate report number
            $year = Carbon::parse($request->survey_date)->format('Y');
            $latestReport = SurveyReport::whereYear('survey_date', $year)->orderBy('id', 'desc')->first();
            $nextNumber = $latestReport ? intval(substr($latestReport->report_number, -4)) + 1 : 1;
            $reportNumber = 'SR-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $report = SurveyReport::create([
                'report_number' => $reportNumber,
                'survey_location' => $request->survey_location,
                'client_name' => $request->client_name,
                'client_address' => $request->client_address,
                'pic_client' => $request->pic_client,
                'phone_number' => $request->phone_number,
                'survey_date' => $request->survey_date,
                'surveyor' => $request->surveyor,
                'opening_description' => $request->opening_description,
                'closing_description' => $request->closing_description,
                'created_by' => auth()->id() ?? 1, // Fallback if no auth
            ]);

            if ($request->tree_data) {
                $treeData = json_decode($request->tree_data, true);
                $catOrder = 1;
                foreach ($treeData as $catData) {
                    $categoryNode = SurveyReportNode::create([
                        'survey_report_id' => $report->id,
                        'parent_id' => null,
                        'title' => $catData['title'],
                        'node_type' => 'category',
                        'sort_order' => $catOrder++
                    ]);

                    if (isset($catData['children'])) {
                        $grpOrder = 1;
                        foreach ($catData['children'] as $grpData) {
                            $groupNode = SurveyReportNode::create([
                                'survey_report_id' => $report->id,
                                'parent_id' => $categoryNode->id,
                                'title' => $grpData['title'],
                                'node_type' => 'group',
                                'sort_order' => $grpOrder++
                            ]);

                            if (isset($grpData['children'])) {
                                $itmOrder = 1;
                                foreach ($grpData['children'] as $itmData) {
                                    $itemNode = SurveyReportNode::create([
                                        'survey_report_id' => $report->id,
                                        'parent_id' => $groupNode->id,
                                        'title' => $itmData['title'],
                                        'node_type' => 'item',
                                        'qty' => $itmData['qty'] ?? null,
                                        'remark' => $itmData['remark'] ?? null,
                                        'sort_order' => $itmOrder++
                                    ]);

                                    // Handle attachments
                                    $frontendNodeId = $itmData['id'];
                                    if ($request->hasFile("attachments.{$frontendNodeId}")) {
                                        $files = $request->file("attachments.{$frontendNodeId}");
                                        $fileOrder = 1;
                                        foreach ($files as $file) {
                                            $path = $file->store('survey_reports', 'public');
                                            SurveyReportAttachment::create([
                                                'survey_report_node_id' => $itemNode->id,
                                                'file_path' => $path,
                                                'sort_order' => $fileOrder++
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('survey-reports.index')
                ]);
            }

            return redirect()->route('survey-reports.index')->with('success', 'Survey Report created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return back()->with('error', 'Error creating report: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $report = SurveyReport::with('nodes.attachments')->findOrFail($id);
        
        $treeData = [];
        $categories = $report->nodes->where('node_type', 'category')->sortBy('sort_order');
        
        foreach ($categories as $cat) {
            $catData = [
                'id' => $cat->id,
                'type' => 'category',
                'title' => $cat->title,
                'children' => []
            ];
            
            $groups = $report->nodes->where('parent_id', $cat->id)->where('node_type', 'group')->sortBy('sort_order');
            foreach ($groups as $grp) {
                $grpData = [
                    'id' => $grp->id,
                    'type' => 'group',
                    'title' => $grp->title,
                    'children' => []
                ];
                
                $items = $report->nodes->where('parent_id', $grp->id)->where('node_type', 'item')->sortBy('sort_order');
                foreach ($items as $itm) {
                    $itemAttachments = $itm->attachments->map(function($att) {
                        return [
                            'id' => $att->id,
                            'url' => asset('storage/' . $att->file_path)
                        ];
                    })->toArray();
                    
                    $itmData = [
                        'id' => $itm->id,
                        'type' => 'item',
                        'title' => $itm->title,
                        'qty' => $itm->qty,
                        'remark' => $itm->remark,
                        'existing_attachments' => $itemAttachments
                    ];
                    
                    $grpData['children'][] = $itmData;
                }
                
                $catData['children'][] = $grpData;
            }
            
            $treeData[] = $catData;
        }

        return view('project_report.survey_reports.edit', compact('report', 'treeData'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'survey_location' => 'required|string|max:255',
            'survey_date' => 'required|date',
            'surveyor' => 'required|string|max:255',
            'tree_data' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $report = SurveyReport::findOrFail($id);
            $report->update([
                'client_name' => $request->client_name,
                'client_address' => $request->client_address,
                'survey_location' => $request->survey_location,
                'pic_client' => $request->pic_client,
                'phone_number' => $request->phone_number,
                'survey_date' => $request->survey_date,
                'surveyor' => $request->surveyor,
                'opening_description' => $request->opening_description,
                'closing_description' => $request->closing_description,
            ]);

            // Existing node tracking
            $existingNodeIds = $report->nodes->pluck('id')->toArray();
            $nodesToKeep = [];
            
            $treeData = json_decode($request->tree_data, true);
            $attachmentsData = $request->file('attachments') ?? [];
            $existingAttachmentsData = $request->input('existing_attachments') ?? [];
            
            $catOrder = 1;
            foreach ($treeData as $catData) {
                // If it looks like a new ID from JS ('node_...'), set it to null
                $catId = strpos($catData['id'], 'node_') === 0 ? null : $catData['id'];
                
                $category = SurveyReportNode::updateOrCreate(
                    ['id' => $catId, 'survey_report_id' => $report->id],
                    [
                        'parent_id' => null,
                        'node_type' => 'category',
                        'title' => $catData['title'],
                        'sort_order' => $catOrder
                    ]
                );
                $nodesToKeep[] = $category->id;
                $catOrder++;

                $grpOrder = 1;
                foreach ($catData['children'] as $grpData) {
                    $grpId = strpos($grpData['id'], 'node_') === 0 ? null : $grpData['id'];
                    $group = SurveyReportNode::updateOrCreate(
                        ['id' => $grpId, 'survey_report_id' => $report->id],
                        [
                            'parent_id' => $category->id,
                            'node_type' => 'group',
                            'title' => $grpData['title'],
                            'sort_order' => $grpOrder
                        ]
                    );
                    $nodesToKeep[] = $group->id;
                    $grpOrder++;

                    $itmOrder = 1;
                    foreach ($grpData['children'] as $itmData) {
                        $itmId = strpos($itmData['id'], 'node_') === 0 ? null : $itmData['id'];
                        $item = SurveyReportNode::updateOrCreate(
                            ['id' => $itmId, 'survey_report_id' => $report->id],
                            [
                                'parent_id' => $group->id,
                                'node_type' => 'item',
                                'title' => $itmData['title'],
                                'qty' => $itmData['qty'] ?? null,
                                'remark' => $itmData['remark'] ?? null,
                                'sort_order' => $itmOrder
                            ]
                        );
                        $nodesToKeep[] = $item->id;
                        $itmOrder++;

                        // Handle existing attachments to keep
                        $keepAttIds = $existingAttachmentsData[$itmData['id']] ?? [];
                        
                        // Delete attachments that belong to this item but are not in $keepAttIds
                        if ($item->id) { // If it was an existing item
                            $oldAttachments = SurveyReportAttachment::where('survey_report_node_id', $item->id)->get();
                            foreach ($oldAttachments as $oldAtt) {
                                if (!in_array($oldAtt->id, $keepAttIds)) {
                                    Storage::disk('public')->delete($oldAtt->file_path);
                                    $oldAtt->delete();
                                }
                            }
                        }

                        // Handle new attachments
                        if (isset($attachmentsData[$itmData['id']])) {
                            $attOrder = SurveyReportAttachment::where('survey_report_node_id', $item->id)->max('sort_order') + 1;
                            foreach ($attachmentsData[$itmData['id']] as $file) {
                                $path = $file->store('survey_reports', 'public');
                                SurveyReportAttachment::create([
                                    'survey_report_node_id' => $item->id,
                                    'file_path' => $path,
                                    'sort_order' => $attOrder
                                ]);
                                $attOrder++;
                            }
                        }
                    }
                }
            }

            // Delete nodes that were removed
            $nodesToDelete = array_diff($existingNodeIds, $nodesToKeep);
            if (count($nodesToDelete) > 0) {
                // Delete attachments of deleted nodes
                $deletedAttachments = SurveyReportAttachment::whereIn('survey_report_node_id', $nodesToDelete)->get();
                foreach ($deletedAttachments as $att) {
                    Storage::disk('public')->delete($att->file_path);
                }
                SurveyReportNode::whereIn('id', $nodesToDelete)->delete();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('survey-reports.index')]);
            }

            return redirect()->route('survey-reports.index')->with('success', 'Survey Report updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return back()->with('error', 'Error updating report: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $report = SurveyReport::findOrFail($id);
        
        // Delete attachments files
        $attachments = SurveyReportAttachment::whereHas('node', function ($q) use ($id) {
            $q->where('survey_report_id', $id);
        })->get();

        foreach ($attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $report->delete();

        return redirect()->route('project.survey-reports.index')->with('success', 'Survey Report deleted successfully');
    }

    public function pdf($id)
    {
        $report = SurveyReport::with(['nodes' => function($q) {
            $q->orderBy('sort_order');
        }, 'nodes.attachments' => function($q) {
            $q->orderBy('sort_order');
        }])->findOrFail($id);

        $flatRows = [];

        $categories = $report->nodes->where('node_type', 'category');
        $catIndex = 1;

        foreach ($categories as $cat) {
            $flatRows[] = [
                'type' => 'category',
                'title' => $cat->title,
                'no' => '',
            ];

            $groups = $report->nodes->where('parent_id', $cat->id)->where('node_type', 'group');
            $grpIndex = 1;
            foreach ($groups as $grp) {
                $flatRows[] = [
                    'type' => 'group',
                    'title' => $grp->title,
                    'no' => $grpIndex,
                ];

                $items = $report->nodes->where('parent_id', $grp->id)->where('node_type', 'item');
                $itmIndex = 1;
                foreach ($items as $itm) {
                    $flatRows[] = [
                        'type' => 'item',
                        'title' => $itm->title,
                        'remark' => $itm->remark,
                        'qty' => $itm->qty,
                        'no' => $grpIndex . '.' . $itmIndex,
                        'attachments' => $itm->attachments
                    ];
                    $itmIndex++;
                }
                $grpIndex++;
            }
            $catIndex++;
        }

        $pdf = Pdf::loadView('project_report.survey_reports.pdf', compact('report', 'flatRows'))
                  ->setPaper('a4', 'portrait');
        
        return $pdf->stream($report->report_number . '.pdf');
    }
}
