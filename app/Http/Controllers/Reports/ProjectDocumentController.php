<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
//
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProjectDocumentController extends Controller
{
    public function __construct()
    {
        // No services needed
    }

    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $search = $request->query('search');
        $category = $request->query('category');
        
        if (!$projectId) {
            return response()->json(['error' => 'Project ID is required'], 400);
        }

        $query = ProjectDocument::with('uploader')
            ->where('project_id', $projectId);
            
        if (!empty($search)) {
            $query->where('document_name', 'like', '%' . $search . '%');
        }
        
        if (!empty($category)) {
            $query->where('document_category', $category);
        }

        $documents = $query->latest()
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_name' => $doc->document_name,
                    'document_category' => $doc->document_category,
                    'uploaded_by_name' => $doc->uploader ? $doc->uploader->name : '-',
                    'created_at_formatted' => $doc->created_at->format('d M Y H:i'),
                    'google_drive_link' => $doc->google_drive_link,
                    'version' => $doc->version,
                    'remarks' => $doc->remarks
                ];
            });

        return response()->json(['documents' => $documents]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'document_name' => 'required|string|max:255',
            'document_category' => 'required|string|max:255',
            'google_drive_link' => 'required|url',
            'version' => 'nullable|string|max:50',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $project = Project::findOrFail($request->project_id);
            
            // Save to database
            $document = new ProjectDocument();
            $document->project_id = $project->id;
            $document->document_name = $request->document_name;
            $document->document_category = $request->document_category;
            $document->google_drive_link = $request->google_drive_link;
            $document->version = $request->version;
            $document->remarks = $request->remarks;
            $document->uploaded_by = auth()->id();
            $document->save();

            return response()->json([
                'success' => true, 
                'message' => 'Document link saved successfully',
                'document' => $document
            ]);

        } catch (\Exception $e) {
            \Log::error("Save failed in Controller: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => "Failed to save document link:\n\n" . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $document = ProjectDocument::findOrFail($id);
            
            // Delete from database
            $document->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error("Delete failed in Controller: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }
}



