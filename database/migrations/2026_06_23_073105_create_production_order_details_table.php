<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('production_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('products');
            $table->decimal('quantity_per_bom', 15, 4);
            $table->decimal('quantity_required', 15, 4);
            $table->decimal('stock_available', 15, 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_order_details');
    }
};
