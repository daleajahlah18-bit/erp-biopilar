<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->after('production_date')->constrained('warehouses');
        });
    }

    public function down()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
