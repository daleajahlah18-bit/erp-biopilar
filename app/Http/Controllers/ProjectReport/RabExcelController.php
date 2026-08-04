<?php

namespace App\Http\Controllers\ProjectReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rab;
use App\Models\Project;
use App\Models\RabNode;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Excel\Exports\RabTemplateExport;
use App\Excel\Exports\RabExport;

class RabExcelController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new RabTemplateExport, 'Template_RAB.xlsx');
    }

    public function export(Rab $rab)
    {
        $rab->load(['nodes' => function($q) {
            $q->orderBy('sort_order');
        }, 'project']);

        return Excel::download(new RabExport($rab), 'RAB_' . $rab->rab_name . '.xlsx');
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'rab_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $project = Project::findOrFail($request->project_id);
        $rab_name = $request->rab_name;

        // Store temporarily
        $path = $request->file('file')->store('temp');
        $fullPath = storage_path('app/' . $path);

        $previewData = [];
        $grandTotal = 0;

        try {
            // Very simple parser for the preview
            $rows = Excel::toArray(new \stdClass(), $fullPath)[0]; // First sheet
            
            // Start reading from row 6 (index 5)
            $rowIndex = 5;
            $currentSection = null;
            $currentGroup = null;

            while ($rowIndex < count($rows)) {
                $row = $rows[$rowIndex];
                $no = $row[0] ?? '';
                $description = $row[1] ?? '';
                
                // If NO and Description are both empty, we might have reached the end
                if (empty($no) && empty($description)) {
                    $rowIndex++;
                    continue;
                }

                $qty = floatval($row[2] ?? 0);
                $unit = $row[3] ?? '';
                $unitPrice = floatval($row[4] ?? 0);
                
                $isSection = preg_match('/^[A-Z]\.?$/', trim($no)) || preg_match('/^[IVX]+\.?$/i', trim($no));
                $isIndented = str_starts_with($description, '  ');

                if (!empty($no) && !empty($description) && $isSection) {
                    // Section
                    $previewData[] = [
                        'type' => 'Section',
                        'no' => $no,
                        'title' => trim($description),
                        'qty' => 0,
                        'unit' => '',
                        'unit_price' => 0,
                        'total_price' => 0,
                        'specification' => ''
                    ];
                } else if (!empty($no) && !empty($description) && !$isIndented) {
                    // Group
                    $previewData[] = [
                        'type' => 'Group',
                        'no' => $no,
                        'title' => trim($description),
                        'qty' => $qty,
                        'unit' => trim($unit),
                        'unit_price' => $unitPrice,
                        'total_price' => $qty * $unitPrice,
                        'specification' => ''
                    ];
                } else if (!empty($description) && (!empty($qty) || !empty($unitPrice))) {
                    // Item
                    $totalPrice = $qty * $unitPrice;
                    $previewData[] = [
                        'type' => 'Item',
                        'no' => $no,
                        'title' => trim($description),
                        'qty' => $qty,
                        'unit' => trim($unit),
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'specification' => ''
                    ];
                    $grandTotal += $totalPrice;
                }
                
                $rowIndex++;
            }
        } catch (\Exception $e) {
            Storage::delete($path);
            return back()->with('error', 'Failed to parse Excel file. Ensure it matches the template format. Error: ' . $e->getMessage());
        }

        $tempFile = $path;
        return view('project_report.rabs.import_preview', compact('previewData', 'project', 'rab_name', 'tempFile', 'grandTotal'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'rab_name' => 'required|string|max:255',
            'temp_file' => 'required|string'
        ]);

        $fullPath = storage_path('app/' . $request->temp_file);
        
        if (!Storage::exists($request->temp_file)) {
            return redirect()->route('rabs.index')->with('error', 'Temporary file not found. Please re-upload.');
        }

        DB::beginTransaction();
        try {
            $rab = Rab::create([
                'project_id' => $request->project_id,
                'rab_name' => $request->rab_name,
                'status' => 'Draft',
                'created_by' => auth()->id() ?? 1,
            ]);

            $rows = Excel::toArray(new \stdClass(), $fullPath)[0];
            
            $rowIndex = 5;
            $currentSection = null;
            $currentGroup = null;
            $grandTotal = 0;
            $sectionTotal = 0;
            $groupTotal = 0;

            $secOrder = 1;
            $grpOrder = 1;
            $itmOrder = 1;

            while ($rowIndex < count($rows)) {
                $row = $rows[$rowIndex];
                $no = $row[0] ?? '';
                $description = $row[1] ?? '';
                
                if (empty($no) && empty($description)) {
                    $rowIndex++;
                    continue;
                }

                $qty = floatval($row[2] ?? 0);
                $unit = $row[3] ?? '';
                $unitPrice = floatval($row[4] ?? 0);
                
                $isSection = preg_match('/^[A-Z]\.?$/', trim($no)) || preg_match('/^[IVX]+\.?$/i', trim($no));
                $isIndented = str_starts_with($description, '  ');

                if (!empty($no) && !empty($description) && $isSection) {
                    // Save previous section total
                    if ($currentSection) {
                        $currentSection->update(['total_price' => $sectionTotal]);
                        $grandTotal += $sectionTotal;
                    }
                    // Save previous group total
                    if ($currentGroup) {
                        $currentGroup->update(['total_price' => $groupTotal]);
                    }

                    $currentSection = RabNode::create([
                        'rab_id' => $rab->id,
                        'parent_id' => null,
                        'title' => trim($description),
                        'node_type' => 'Section',
                        'sort_order' => $secOrder++
                    ]);
                    $sectionTotal = 0;
                    $currentGroup = null;
                    $grpOrder = 1;
                } else if (!empty($no) && !empty($description) && !$isIndented) {
                    // Group
                    // Save previous group total
                    if ($currentGroup) {
                        $currentGroup->update(['total_price' => $groupTotal]);
                    }
                    
                    // If we have a group but no section yet (malformed excel), create a dummy section
                    if (!$currentSection) {
                        $currentSection = RabNode::create([
                            'rab_id' => $rab->id,
                            'parent_id' => null,
                            'title' => 'Default Section',
                            'node_type' => 'Section',
                            'sort_order' => $secOrder++
                        ]);
                        $sectionTotal = 0;
                    }

                    $groupOwnTotal = $qty * $unitPrice;
                    $currentGroup = RabNode::create([
                        'rab_id' => $rab->id,
                        'parent_id' => $currentSection->id,
                        'title' => trim($description),
                        'node_type' => 'Group',
                        'qty' => $qty,
                        'unit' => trim($unit),
                        'unit_price' => $unitPrice,
                        'total_price' => $groupOwnTotal,
                        'sort_order' => $grpOrder++
                    ]);
                    $groupTotal = $groupOwnTotal;
                    $itmOrder = 1;
                } else if (!empty($description) && (!empty($qty) || !empty($unitPrice))) {
                    // Item
                    $totalPrice = $qty * $unitPrice;
                    
                    if (!$currentSection) {
                        $currentSection = RabNode::create([
                            'rab_id' => $rab->id,
                            'parent_id' => null,
                            'title' => 'Default Section',
                            'node_type' => 'Section',
                            'sort_order' => $secOrder++
                        ]);
                        $sectionTotal = 0;
                    }

                    if (!$currentGroup) {
                        $currentGroup = RabNode::create([
                            'rab_id' => $rab->id,
                            'parent_id' => $currentSection->id,
                            'title' => 'Default Group',
                            'node_type' => 'Group',
                            'sort_order' => $grpOrder++
                        ]);
                        $groupTotal = 0;
                    }

                    RabNode::create([
                        'rab_id' => $rab->id,
                        'parent_id' => $currentGroup->id,
                        'title' => trim($description),
                        'node_type' => 'Item',
                        'qty' => $qty,
                        'unit' => trim($unit),
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'sort_order' => $itmOrder++
                    ]);
                    
                    $groupTotal += $totalPrice;
                    $sectionTotal += $totalPrice;
                }
                
                $rowIndex++;
            }

            // Save last group & section totals
            if ($currentGroup) {
                $currentGroup->update(['total_price' => $groupTotal]);
            }
            if ($currentSection) {
                $currentSection->update(['total_price' => $sectionTotal]);
                $grandTotal += $sectionTotal;
            }

            $rab->update(['total_amount' => $grandTotal]);

            DB::commit();
            Storage::delete($request->temp_file);

            return redirect()->route('rabs.index')->with('success', 'RAB successfully imported.');
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($request->temp_file);
            return redirect()->route('rabs.index')->with('error', 'Error during import: ' . $e->getMessage());
        }
    }
}
