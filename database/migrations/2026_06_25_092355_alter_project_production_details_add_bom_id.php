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
            $table->foreignId('bill_of_material_id')->nullable()->after('product_id')->constrained('bill_of_materials')->nullOnDelete();
            $table->decimal('bom_hpp', 18, 2)->default(0)->after('last_purchase_price');
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
            $table->dropForeign(['bill_of_material_id']);
            $table->dropColumn(['bill_of_material_id', 'bom_hpp']);
        });
    }
};
