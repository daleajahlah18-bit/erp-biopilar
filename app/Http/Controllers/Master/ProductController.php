<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Product::class, strtolower('Product'));
    }
    public function index()
    {
        $products = Product::with(['unit', 'creator'])->sortable()->latest()->sortable()->paginate(10);
        return view('master.products.index', compact('products'));
    }

    public function create()
    {
        $units = Unit::all();
        return view('master.products.create', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'required|unique:products,product_code',
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|in:Bahan Baku,Bahan Jadi,Bill of Material',
            'engineering_category' => 'required|in:Civil,Mechanical,Electrical',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string'
        ]);

        Product::create([
            'product_code' => $request->product_code,
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'engineering_category' => $request->engineering_category,
            'unit_id' => $request->unit_id,
            'description' => $request->description,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('master.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['unit', 'creator']);
        return view('master.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $units = Unit::all();
        return view('master.products.edit', compact('product', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_code' => 'required|unique:products,product_code,' . $product->id,
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|in:Bahan Baku,Bahan Jadi,Bill of Material',
            'engineering_category' => 'required|in:Civil,Mechanical,Electrical',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string'
        ]);

        $product->update($request->only('product_code', 'product_name', 'product_type', 'engineering_category', 'unit_id', 'description'));

        return redirect()->route('master.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('master.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
