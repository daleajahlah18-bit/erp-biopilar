<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\AssetMaintenance;
use App\Models\AssetImprovement;
use Illuminate\Http\Request;

class AssetReportController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::with('category')->get();
        return view('asset_management.reports.index', compact('assets'));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->query('type');
        
        // In a real application we would use DOMPDF or Snappy to generate PDF
        // For now, we will return a simple print view which the user can save as PDF
        $assets = Asset::with(['category', 'improvements', 'maintenances', 'movements'])->get();
        
        return view('asset_management.reports.pdf', compact('assets', 'type'));
    }
}
