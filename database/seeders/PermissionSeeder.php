<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\SystemMenu;
use App\Models\User;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $menus = SystemMenu::all();
        $actions = ['visible', 'view', 'create', 'edit', 'delete', 'approve', 'export', 'import', 'print'];

        foreach ($menus as $menu) {
            $menuSlug = Str::slug($menu->name, '_');
            
            foreach ($actions as $action) {
                $permissionName = $menuSlug . '.' . $action;
                Permission::findOrCreate($permissionName);
            }
        }

        // Create Default Administrator Role
        $adminRole = Role::findOrCreate('Administrator');
        
        // Give all permissions to Administrator
        $adminRole->givePermissionTo(Permission::all());

        // Assign Administrator role to user ID 1 (Assuming there is one, or create it)
        $user = User::find(1);
        if ($user) {
            $user->assignRole('Administrator');
        } else {
            $user = User::create([
                'name' => 'Administrator',
                'email' => 'admin@biopilar.com',
                'password' => bcrypt('password'), // Or your default password
                'employee_id' => 'EMP-001',
                'department' => 'IT',
                'position' => 'System Administrator',
                'status' => 'Active',
            ]);
            $user->assignRole('Administrator');
        }
    }
}
