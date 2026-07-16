<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_production_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_production_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity', 15, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('stock_before', 15, 4)->default(0);
            $table->decimal('stock_after', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_production_details');
    }
};
