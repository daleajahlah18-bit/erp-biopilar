<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index() { $units = Unit::latest()->sortable()->paginate(10); return view('master.units.index', compact('units')); }
    public function create() { return view('master.units.create'); }
    public function store(Request $request) {
        $request->validate(['unit_name' => 'required|unique:units,unit_name']);
        Unit::create($request->all());
        return redirect()->route('master.units.index')->with('success', 'Unit created.');
    }
    public function show(Unit $unit) {}
    public function edit(Unit $unit) { return view('master.units.edit', compact('unit')); }
    public function update(Request $request, Unit $unit) {
        $request->validate(['unit_name' => 'required|unique:units,unit_name,'.$unit->id]);
        $unit->update($request->all());
        return redirect()->route('master.units.index')->with('success', 'Unit updated.');
    }
    public function destroy(Unit $unit) { $unit->delete(); return redirect()->route('master.units.index')->with('success', 'Unit deleted.'); }
}