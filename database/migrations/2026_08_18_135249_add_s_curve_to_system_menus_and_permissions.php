<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemMenu;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $menu = SystemMenu::create([
            'name' => 'S-Curve',
            'parent' => 'Project Report'
        ]);

        $actions = ['visible', 'view', 'create', 'edit', 'delete', 'approve', 'export', 'print'];
        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name' => 's_curve.' . $action,
                'guard_name' => 'web'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SystemMenu::where('name', 'S-Curve')->delete();

        $actions = ['visible', 'view', 'create', 'edit', 'delete', 'approve', 'export', 'print'];
        foreach ($actions as $action) {
            Permission::where('name', 's_curve.' . $action)->delete();
        }
    }
};
