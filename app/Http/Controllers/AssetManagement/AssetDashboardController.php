<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDashboardController extends Controller
{
    public function index()
    {
        $assets = Asset::all();
        $totalAssets = $assets->count();
        $totalCost = $assets->sum('acquisition_cost');
        
        // Sum dynamic latest book value for Commercial
        $commercialBookValue = $assets->sum(function ($a) {
            return $a->commercial_book_value;
        });

        // Sum dynamic latest book value for Fiscal
        $fiscalBookValue = $assets->sum(function ($a) {
            return $a->fiscal_book_value;
        });

        $assetsUnderMaintenance = $assets->where('status', 'Under Maintenance')->count();
        
        $fullyDepreciated = $assets->filter(function ($a) {
            return $a->commercial_remaining_life <= 0;
        })->count();
        
        return view('asset_management.dashboard.index', compact(
            'totalAssets', 'totalCost', 'commercialBookValue', 'fiscalBookValue', 'assetsUnderMaintenance', 'fullyDepreciated'
        ));
    }
}
