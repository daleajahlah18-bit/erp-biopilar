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
        Schema::table('report_phases', function (Blueprint $table) {
            $table->string('client_sign_name_3')->nullable()->after('client_sign_position_2');
            $table->string('client_sign_position_3')->nullable()->after('client_sign_name_3');
            $table->string('client_sign_name_4')->nullable()->after('client_sign_position_3');
            $table->string('client_sign_position_4')->nullable()->after('client_sign_name_4');
            $table->string('company_sign_name_3')->nullable()->after('company_sign_position_2');
            $table->string('company_sign_position_3')->nullable()->after('company_sign_name_3');
            $table->string('company_sign_name_4')->nullable()->after('company_sign_position_3');
            $table->string('company_sign_position_4')->nullable()->after('company_sign_name_4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_phases', function (Blueprint $table) {
            $table->dropColumn([
                'client_sign_name_3', 'client_sign_position_3',
                'client_sign_name_4', 'client_sign_position_4',
                'company_sign_name_3', 'company_sign_position_3',
                'company_sign_name_4', 'company_sign_position_4',
            ]);
        });
    }
};
