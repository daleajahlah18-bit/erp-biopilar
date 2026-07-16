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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('engineering_category', ['Civil', 'Mechanical', 'Electrical'])->default('Mechanical')->after('product_type');
        });

        // Randomize the engineering_category for existing products to vary them as requested by user
        \Illuminate\Support\Facades\DB::statement("UPDATE products SET engineering_category = ELT(FLOOR(1 + (RAND() * 3)), 'Civil', 'Mechanical', 'Electrical')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('engineering_category');
        });
    }
};
