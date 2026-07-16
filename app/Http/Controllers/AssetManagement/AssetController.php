<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AssetController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Asset::class, strtolower('Asset'));
    }
    public function index()
    {
        $assets = Asset::with('category')->get();
        return view('asset_management.assets.index', compact('assets'));
    }

    public function create()
    {
        $categories = AssetCategory::all();
        return view('asset_management.assets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'asset_description' => 'nullable|string',
            'location' => 'nullable|string',
            'department' => 'nullable|string',
            'responsible_person' => 'nullable|string',
            'purchase_date' => 'required|date',
            'start_depreciation_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'residual_value' => 'required|numeric|min:0',
            'commercial_method' => 'required|string',
            'commercial_useful_life' => 'required|integer|min:1',
            'fiscal_method' => 'required|string',
            'fiscal_useful_life' => 'required|integer|min:1',
            'vendor' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        $year = Carbon::now()->format('Y');
        $count = Asset::whereYear('created_at', $year)->count() + 1;
        $validated['asset_code'] = 'AST-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        $validated['status'] = 'Active';

        Asset::create($validated);
        return redirect()->route('asset-management.assets.index')->with('success', 'Asset created successfully');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'maintenances', 'improvements', 'movements']);

        $commercialDepreciations = $asset->commercial_schedule;
        $fiscalDepreciations = $asset->fiscal_schedule;

        return view('asset_management.assets.show', compact('asset', 'commercialDepreciations', 'fiscalDepreciations'));
    }

    public function edit(Asset $asset)
    {
        $categories = AssetCategory::all();
        return view('asset_management.assets.edit', compact('asset', 'categories'));
    }

    public function update(Request $request, Asset $asset)
    {
        $asset->update($request->all());
        return redirect()->route('asset-management.assets.index')->with('success', 'Asset updated successfully');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('asset-management.assets.index')->with('success', 'Asset deleted successfully');
    }

    public function storeMaintenance(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        
        $asset->maintenances()->create($validated);
        return back()->with('success', 'Maintenance record added successfully');
    }

    public function storeImprovement(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'improvement_date' => 'required|date',
            'improvement_cost' => 'required|numeric|min:0',
            'vendor' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        
        // These fields are required by DB schema but we now dynamically calculate them.
        // We will just store the snapshot of what they are TODAY, but the engine doesn't rely on it.
        $validated['previous_book_value_commercial'] = $asset->commercial_book_value;
        $validated['new_book_value_commercial'] = $asset->commercial_book_value + $validated['improvement_cost'];
        $validated['previous_book_value_fiscal'] = $asset->fiscal_book_value;
        $validated['new_book_value_fiscal'] = $asset->fiscal_book_value + $validated['improvement_cost'];
        
        $asset->improvements()->create($validated);
        return back()->with('success', 'Capital Improvement recorded. Depreciation base has been updated.');
    }

    public function storeMovement(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'movement_date' => 'required|date',
            'from_department' => 'nullable|string',
            'to_department' => 'required|string',
            'from_location' => 'nullable|string',
            'to_location' => 'required|string',
            'from_pic' => 'nullable|string',
            'to_pic' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        
        $asset->movements()->create($validated);
        
        $asset->update([
            'department' => $validated['to_department'],
            'location' => $validated['to_location'],
            'responsible_person' => $validated['to_pic']
        ]);

        return back()->with('success', 'Asset movement recorded successfully.');
    }
}
