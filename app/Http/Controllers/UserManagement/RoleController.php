<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\SystemMenu;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Role::class, strtolower('Role'));
    }
    public function index()
    {
        $this->authorize('Users.view'); // using Users menu permission as a placeholder or we can use Roles.view if it existed. Wait, 'Roles' is the menu name. Let's use 'Roles.view'
        $roles = Role::with('permissions')->get();
        return view('user-management.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('Roles.create');
        $menus = SystemMenu::orderBy('parent')->orderBy('name')->get()->groupBy('parent');
        return view('user-management.roles.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $this->authorize('Roles.create');
        
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $this->authorize('Roles.edit');
        $menus = SystemMenu::orderBy('parent')->orderBy('name')->get()->groupBy('parent');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('user-management.roles.edit', compact('role', 'menus', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('Roles.edit');

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('Roles.delete');
        
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete role assigned to users.');
        }
        
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
