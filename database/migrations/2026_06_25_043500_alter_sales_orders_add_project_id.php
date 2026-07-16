<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Make customer_id nullable safely
        DB::statement('ALTER TABLE sales_orders MODIFY customer_id BIGINT UNSIGNED NULL;');

        Schema::table('sales_orders', function (Blueprint $table) {
            // 2. Add project_id
            $table->foreignId('project_id')->nullable()->after('sales_order_number')->constrained('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
        
        DB::statement('ALTER TABLE sales_orders MODIFY customer_id BIGINT UNSIGNED NOT NULL;');
    }
};
