<?php

namespace App\Http\Controllers\ProjectReport;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ReportPhase;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use DB;

class ReportPhaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:report_phase.visible')->only(['index', 'show']);
        $this->middleware('permission:report_phase.create')->only(['create', 'store']);
        $this->middleware('permission:report_phase.edit')->only(['edit', 'update']);
        $this->middleware('permission:report_phase.delete')->only('destroy');
        $this->middleware('permission:report_phase.export')->only('exportPdf');
    }

    public function index()
    {
        $reportPhases = ReportPhase::with('project')->orderBy('created_at', 'desc')->get();
        return view('project_report.report_phases.index', compact('reportPhases'));
    }

    public function create()
    {
        $projects = Project::orderBy('project_name')->get();
        return view('project_report.report_phases.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'document_date' => 'required|date',
            'progress_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $reportNumber = $this->generateReportNumber();

        DB::beginTransaction();
        try {
            $project = Project::findOrFail($request->project_id);
            $project->update([
                'client_po_number' => $request->client_po_number,
                'contract_number' => $request->contract_number,
            ]);

            $reportPhase = ReportPhase::create([
                'project_id' => $project->id,
                'report_number' => $reportNumber,
                'document_date' => $request->document_date,
                'progress_percentage' => $request->progress_percentage,
                'client_sign_name_1' => $request->client_sign_name_1,
                'client_sign_position_1' => $request->client_sign_position_1,
                'client_sign_name_2' => $request->client_sign_name_2,
                'client_sign_position_2' => $request->client_sign_position_2,
                'client_sign_name_3' => $request->client_sign_name_3,
                'client_sign_position_3' => $request->client_sign_position_3,
                'client_sign_name_4' => $request->client_sign_name_4,
                'client_sign_position_4' => $request->client_sign_position_4,
                'company_sign_name_1' => $request->company_sign_name_1,
                'company_sign_position_1' => $request->company_sign_position_1,
                'company_sign_name_2' => $request->company_sign_name_2,
                'company_sign_position_2' => $request->company_sign_position_2,
                'company_sign_name_3' => $request->company_sign_name_3,
                'company_sign_position_3' => $request->company_sign_position_3,
                'company_sign_name_4' => $request->company_sign_name_4,
                'company_sign_position_4' => $request->company_sign_position_4,
                'opening_paragraph' => $request->opening_paragraph,
                'progress_paragraph' => $request->progress_paragraph,
                'closing_paragraph' => $request->closing_paragraph,
                'additional_notes' => $request->additional_notes,
                'created_by' => auth()->id()
            ]);

            activity()
                ->performedOn($reportPhase)
                ->causedBy(auth()->user())
                ->log("Created Report Phase:\n{$reportNumber}");

            if ($request->hasAny(['opening_paragraph', 'progress_paragraph', 'closing_paragraph', 'additional_notes'])) {
                activity()
                    ->performedOn($reportPhase)
                    ->causedBy(auth()->user())
                    ->log("Created Report Phase Narrative:\n{$reportNumber}");
            }

            DB::commit();
            return redirect()->route('report-phases.index')->with('success', 'Report Phase berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(ReportPhase $reportPhase)
    {
        $projects = Project::orderBy('project_name')->get();
        return view('project_report.report_phases.edit', compact('reportPhase', 'projects'));
    }

    public function update(Request $request, ReportPhase $reportPhase)
    {
        $request->validate([
            'document_date' => 'required|date',
            'progress_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $oldProgress = $reportPhase->progress_percentage;
        $newProgress = $request->progress_percentage;

        DB::beginTransaction();
        try {
            $reportPhase->project->update([
                'client_po_number' => $request->client_po_number,
                'contract_number' => $request->contract_number,
            ]);

            $reportPhase->update([
                'document_date' => $request->document_date,
                'progress_percentage' => $request->progress_percentage,
                'client_sign_name_1' => $request->client_sign_name_1,
                'client_sign_position_1' => $request->client_sign_position_1,
                'client_sign_name_2' => $request->client_sign_name_2,
                'client_sign_position_2' => $request->client_sign_position_2,
                'client_sign_name_3' => $request->client_sign_name_3,
                'client_sign_position_3' => $request->client_sign_position_3,
                'client_sign_name_4' => $request->client_sign_name_4,
                'client_sign_position_4' => $request->client_sign_position_4,
                'company_sign_name_1' => $request->company_sign_name_1,
                'company_sign_position_1' => $request->company_sign_position_1,
                'company_sign_name_2' => $request->company_sign_name_2,
                'company_sign_position_2' => $request->company_sign_position_2,
                'company_sign_name_3' => $request->company_sign_name_3,
                'company_sign_position_3' => $request->company_sign_position_3,
                'company_sign_name_4' => $request->company_sign_name_4,
                'company_sign_position_4' => $request->company_sign_position_4,
                'opening_paragraph' => $request->opening_paragraph,
                'progress_paragraph' => $request->progress_paragraph,
                'closing_paragraph' => $request->closing_paragraph,
                'additional_notes' => $request->additional_notes,
            ]);

            if ($oldProgress != $newProgress) {
                activity()
                    ->performedOn($reportPhase)
                    ->causedBy(auth()->user())
                    ->log("Updated Report Phase:\n{$reportPhase->report_number}\nProgress: {$oldProgress}% -> {$newProgress}%");
            } else {
                activity()
                    ->performedOn($reportPhase)
                    ->causedBy(auth()->user())
                    ->log("Updated Report Phase: {$reportPhase->report_number}");
            }

            if ($request->hasAny(['opening_paragraph', 'progress_paragraph', 'closing_paragraph', 'additional_notes'])) {
                activity()
                    ->performedOn($reportPhase)
                    ->causedBy(auth()->user())
                    ->log("Updated Report Phase Narrative:\n{$reportPhase->report_number}");
            }

            DB::commit();
            return redirect()->route('report-phases.index')->with('success', 'Report Phase berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(ReportPhase $reportPhase)
    {
        $reportNumber = $reportPhase->report_number;
        $reportPhase->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Deleted Report Phase:\n{$reportNumber}");

        return response()->json(['success' => true]);
    }

    public function exportPdf(ReportPhase $reportPhase)
    {
        activity()
            ->performedOn($reportPhase)
            ->causedBy(auth()->user())
            ->log("Exported PDF:\n{$reportPhase->report_number}");
            
        activity()
            ->performedOn($reportPhase)
            ->causedBy(auth()->user())
            ->log("Generated Report Phase PDF:\n{$reportPhase->report_number}");

        $project = $reportPhase->project;

        $defaultOpening = "Pada hari ini, {{ report_date }} telah diadakan pemeriksaan bersama atas pekerjaan:";
        $defaultProgress = "Dengan ini secara bersama-sama menyatakan bahwa pekerjaan telah mencapai progress {{ progress_percentage }}% dengan baik sesuai dengan PO yang telah diterbitkan oleh {{ client_name }}. Hitungan progress pekerjaan terlampir.";
        $defaultClosing = "Demikian Berita Acara Progress Pekerjaan ini kami buat dengan sebenarnya agar dapat digunakan sebagaimana mestinya.";

        $reportPhase->opening_paragraph = $this->replacePlaceholders($reportPhase->opening_paragraph ?? $defaultOpening, $reportPhase, $project);
        $reportPhase->progress_paragraph = $this->replacePlaceholders($reportPhase->progress_paragraph ?? $defaultProgress, $reportPhase, $project);
        $reportPhase->closing_paragraph = $this->replacePlaceholders($reportPhase->closing_paragraph ?? $defaultClosing, $reportPhase, $project);
        $reportPhase->additional_notes = $reportPhase->additional_notes ? $this->replacePlaceholders($reportPhase->additional_notes, $reportPhase, $project) : null;

        $pdf = PDF::loadView('project_report.report_phases.pdf', compact('reportPhase'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream(str_replace('/', '_', $reportPhase->report_number) . '.pdf');
    }

    private function replacePlaceholders($text, $reportPhase, $project)
    {
        if (!$text) return $text;

        $replacements = [
            '{{ report_date }}' => $reportPhase->document_date ? \Carbon\Carbon::parse($reportPhase->document_date)->locale('id')->isoFormat('D MMMM Y') : '-',
            '{{ project_name }}' => $project->project_name ?? '-',
            '{{ client_name }}' => $project->client_name ?? '-',
            '{{ progress_percentage }}' => $reportPhase->progress_percentage ?? '-',
            '{{ project_address }}' => $project->project_address ?? '-',
            '{{ field_of_work }}' => $project->field_of_work ?? '-',
            '{{ project_start_date }}' => $project->project_start_date ? \Carbon\Carbon::parse($project->project_start_date)->locale('id')->isoFormat('D MMMM Y') : '-',
            '{{ project_end_date }}' => $project->project_end_date ? \Carbon\Carbon::parse($project->project_end_date)->locale('id')->isoFormat('D MMMM Y') : '-',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    private function generateReportNumber()
    {
        $year = date('Y');
        $month = date('n');
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];

        $lastReport = ReportPhase::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastReport) {
            $runningNumber = '001';
        } else {
            $lastNumber = intval(substr($lastReport->report_number, 0, 3));
            $runningNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return "{$runningNumber}/BAPP/BPU/{$romanMonth}/{$year}";
    }

    public function getProjectDetails($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'project_name' => $project->project_name,
                'client_name' => $project->client_name,
                'client_po_number' => $project->client_po_number,
                'contract_number' => $project->contract_number,
                'client_po_date' => $project->client_po_date ? $project->client_po_date->format('Y-m-d') : null,
                'project_address' => $project->project_address, // Or Work Description if there's another field. We use project_name as description usually.
                'client_logo' => $project->client_logo ? asset('storage/' . $project->client_logo) : null,
            ]
        ]);
    }
}
