<?php

$controllers = [
    'Master/SupplierController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index() { $suppliers = Supplier::latest()->paginate(10); return view('master.suppliers.index', compact('suppliers')); }
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
EOT,
    'Master/UnitController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index() { $units = Unit::latest()->paginate(10); return view('master.units.index', compact('units')); }
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
EOT,
    'Master/ProjectController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index() { $projects = Project::latest()->paginate(10); return view('master.projects.index', compact('projects')); }
    public function create() { return view('master.projects.create'); }
    public function store(Request $request) {
        $request->validate(['project_name' => 'required']);
        Project::create($request->all());
        return redirect()->route('master.projects.index')->with('success', 'Project created.');
    }
    public function show(Project $project) {}
    public function edit(Project $project) { return view('master.projects.edit', compact('project')); }
    public function update(Request $request, Project $project) {
        $request->validate(['project_name' => 'required']);
        $project->update($request->all());
        return redirect()->route('master.projects.index')->with('success', 'Project updated.');
    }
    public function destroy(Project $project) { $project->delete(); return redirect()->route('master.projects.index')->with('success', 'Project deleted.'); }
}
EOT,
    'Master/WarehouseController' => <<<'EOT'
<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index() { $warehouses = Warehouse::latest()->paginate(10); return view('master.warehouses.index', compact('warehouses')); }
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
EOT,
];

foreach ($controllers as $name => $content) {
    file_put_contents(__DIR__ . "/app/Http/Controllers/{$name}.php", $content);
    echo "Updated $name\n";
}

$views = [
    'master/suppliers/index.blade.php' => "@extends('layouts.app')\n@section('page_title', 'Master Supplier')\n@section('content') <div class='card'><div class='card-body'>Under Construction (Phase 2). Data ter-load: {{ \$suppliers->total() }}</div></div> @endsection",
    'master/units/index.blade.php' => "@extends('layouts.app')\n@section('page_title', 'Master Unit')\n@section('content') <div class='card'><div class='card-body'>Under Construction (Phase 2). Data ter-load: {{ \$units->total() }}</div></div> @endsection",
    'master/projects/index.blade.php' => "@extends('layouts.app')\n@section('page_title', 'Master Project')\n@section('content') <div class='card'><div class='card-body'>Under Construction (Phase 2). Data ter-load: {{ \$projects->total() }}</div></div> @endsection",
    'master/warehouses/index.blade.php' => "@extends('layouts.app')\n@section('page_title', 'Master Gudang')\n@section('content') <div class='card'><div class='card-body'>Under Construction (Phase 2). Data ter-load: {{ \$warehouses->total() }}</div></div> @endsection",
];

foreach ($views as $path => $content) {
    $dir = dirname(__DIR__ . "/resources/views/{$path}");
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents(__DIR__ . "/resources/views/{$path}", $content);
    echo "Updated view $path\n";
}
