<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:finance.view')->only(['index']);
        $this->middleware('permission:finance.create')->only(['create', 'store']);
        $this->middleware('permission:finance.edit')->only(['edit', 'update']);
        $this->middleware('permission:finance.delete')->only(['destroy']);
    }

    public function index()
    {
        $categories = FinanceExpenseCategory::sortable()->latest()->paginate(10);
        return view('finance.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('finance.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category = FinanceExpenseCategory::create($request->all());

        return redirect()->route('finance.categories.index')->with('success', 'Category created successfully');
    }

    public function edit(FinanceExpenseCategory $category)
    {
        return view('finance.categories.edit', compact('category'));
    }

    public function update(Request $request, FinanceExpenseCategory $category)
    {
        $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($request->all());

        return redirect()->route('finance.categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(FinanceExpenseCategory $category)
    {
        if ($category->expenses()->exists()) {
            return redirect()->route('finance.categories.index')->with('error', 'Cannot delete category because it has expenses.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('finance.categories.index')->with('success', 'Category deleted successfully');
    }
}
