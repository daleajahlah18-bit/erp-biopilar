<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\SystemMenu;

return new class extends Migration
{
    public function up()
    {
        // 1. Create System Menu
        $financeMenu = SystemMenu::create([
            'name' => 'Finance',
            'parent_id' => null,
            'url' => 'finance',
            'icon' => 'fas fa-wallet', // Provide a default icon
            'order' => 10, // Try to place it near the end
        ]);

        // 2. Create Permissions
        $actions = ['visible', 'view', 'create', 'edit', 'delete', 'approve', 'export', 'print'];
        $adminRole = Role::where('name', 'Super Admin')->first();

        foreach ($actions as $action) {
            $permissionName = 'finance.' . $action;
            $permission = Permission::firstOrCreate(['name' => $permissionName]);

            if ($adminRole) {
                $adminRole->givePermissionTo($permission);
            }
        }
    }

    public function down()
    {
        $actions = ['visible', 'view', 'create', 'edit', 'delete', 'approve', 'export', 'print'];
        
        foreach ($actions as $action) {
            Permission::where('name', 'finance.' . $action)->delete();
        }

        SystemMenu::where('name', 'Finance')->delete();
    }
};
