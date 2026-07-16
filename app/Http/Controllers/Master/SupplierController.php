<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Supplier::class, strtolower('Supplier'));
    }
    public function index() { $suppliers = Supplier::latest()->sortable()->paginate(10); return view('master.suppliers.index', compact('suppliers')); }
    public function create() { return view('master.suppliers.create'); }
    public function store(Request $request) {
        $request->validate(['supplier_name' => 'required']);
        Supplier::create($request->all());
        return redirect()->route('master.suppliers.index')->with('success', 'Supplier created.');
    }
    public function show(Supplier $supplier) {}
    public function edit(Supplier $supplier) { return view('master.suppliers.edit', compact('supplier')); }
    public function update(Request $request, Supplier $supplier) {
        $request->validate(['supplier_name' => 'required']);
        $supplier->update($request->all());
        return redirect()->route('master.suppliers.index')->with('success', 'Supplier updated.');
    }
    public function destroy(Supplier $supplier) { $supplier->delete(); return redirect()->route('master.suppliers.index')->with('success', 'Supplier deleted.'); }
}