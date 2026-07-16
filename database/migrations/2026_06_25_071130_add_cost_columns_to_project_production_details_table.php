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
        Schema::table('project_production_details', function (Blueprint $table) {
            $table->decimal('last_purchase_price', 18, 2)->default(0)->after('stock_after');
            $table->decimal('material_cost', 18, 2)->default(0)->after('last_purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_production_details', function (Blueprint $table) {
            $table->dropColumn(['last_purchase_price', 'material_cost']);
        });
    }
};
