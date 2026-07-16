<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index() { $warehouses = Warehouse::latest()->sortable()->paginate(10); return view('master.warehouses.index', compact('warehouses')); }
    public function create() { return view('master.warehouses.create'); }
    public function store(Request $request) {
        $request->validate(['warehouse_name' => 'required']);
        Warehouse::create($request->all());
        return redirect()->route('master.warehouses.index')->with('success', 'Warehouse created.');
    }
    public function show(Warehouse $warehouse) {}
    public function edit(Warehouse $warehouse) { return view('master.warehouses.edit', compact('warehouse')); }
    public function update(Request $request, Warehouse $warehouse) {
        $request->validate(['warehouse_name' => 'required']);
        $warehouse->update($request->all());
        return redirect()->route('master.warehouses.index')->with('success', 'Warehouse updated.');
    }
    public function destroy(Warehouse $warehouse) { $warehouse->delete(); return redirect()->route('master.warehouses.index')->with('success', 'Warehouse deleted.'); }
}