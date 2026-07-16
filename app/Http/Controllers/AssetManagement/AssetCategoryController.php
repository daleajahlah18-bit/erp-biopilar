<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::all();
        return view('asset_management.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'default_useful_life_commercial' => 'required|integer|min:1',
            'default_method_commercial' => 'required|string',
            'default_useful_life_fiscal' => 'required|integer|min:1',
            'default_method_fiscal' => 'required|string',
            'default_residual_value_percent' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $validated['category_code'] = 'CAT-' . strtoupper(substr(uniqid(), -5));

        AssetCategory::create($validated);
        return redirect()->route('asset-management.categories.index')->with('success', 'Asset Category created successfully');
    }

    public function update(Request $request, AssetCategory $category)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'default_useful_life_commercial' => 'required|integer|min:1',
            'default_method_commercial' => 'required|string',
            'default_useful_life_fiscal' => 'required|integer|min:1',
            'default_method_fiscal' => 'required|string',
            'default_residual_value_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $category->update($validated);
        return redirect()->route('asset-management.categories.index')->with('success', 'Asset Category updated successfully');
    }

    public function destroy(AssetCategory $category)
    {
        $category->delete();
        return redirect()->route('asset-management.categories.index')->with('success', 'Asset Category deleted successfully');
    }
}
