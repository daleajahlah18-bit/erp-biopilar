<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('client_logo')->nullable()->after('project_status');
            $table->string('field_of_work')->nullable()->after('client_logo');
            $table->string('work_package')->nullable()->after('field_of_work');
            $table->string('client_user_name')->nullable()->after('work_package');
            $table->string('executor_name')->nullable()->after('client_user_name');
            $table->string('contract_number')->nullable()->after('executor_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'client_logo',
                'field_of_work',
                'work_package',
                'client_user_name',
                'executor_name',
                'contract_number'
            ]);
        });
    }
};
