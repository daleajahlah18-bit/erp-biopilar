<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // $table->dropForeign(['product_id']);
            // $table->dropColumn('product_id');
            // $table->dropColumn('person_in_charge');
            
            // $table->decimal('actual_quantity', 15, 4)->nullable()->after('quantity_target');
            // $table->text('production_result_notes')->nullable()->after('actual_quantity');
        });
        
        DB::statement('ALTER TABLE production_orders CHANGE quantity_target target_quantity DECIMAL(15,4) NOT NULL;');
        
        // Enum change using raw DB statement because doctrine/dbal might have issues with enums
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN status ENUM('Draft', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Draft'");

        Schema::table('production_order_details', function (Blueprint $table) {
            $table->dropForeign(['raw_material_id']);
        });
        
        DB::statement('ALTER TABLE production_order_details CHANGE raw_material_id product_id BIGINT UNSIGNED NOT NULL;');
        
        Schema::table('production_order_details', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        // Add back old columns
    }
};
