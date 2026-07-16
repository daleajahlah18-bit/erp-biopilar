<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::table('bill_of_materials', function (Blueprint $table) {
        //     $table->string('bom_name')->after('bom_number')->nullable();
        //     $table->decimal('total_hpp', 18, 2)->default(0)->after('product_id');
        //     $table->text('notes')->nullable()->after('total_hpp');
        // });

        // Make product_id nullable using raw SQL to avoid dbal dependency issues
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE bill_of_materials MODIFY product_id BIGINT UNSIGNED NULL;');

        // Alter bill_of_material_details
        // Schema::table('bill_of_material_details', function (Blueprint $table) {
        //     $table->dropForeign(['raw_material_id']);
        //     $table->decimal('unit_cost', 18, 2)->default(0)->after('unit_id');
        //     $table->decimal('subtotal', 18, 2)->default(0)->after('unit_cost');
        // });

        // \Illuminate\Support\Facades\DB::statement('ALTER TABLE bill_of_material_details CHANGE raw_material_id product_id BIGINT UNSIGNED NOT NULL;');

        // Schema::table('bill_of_material_details', function (Blueprint $table) {
        //     $table->foreign('product_id')->references('id')->on('products');
        // });
    }

    public function down()
    {
        // 
    }
};
