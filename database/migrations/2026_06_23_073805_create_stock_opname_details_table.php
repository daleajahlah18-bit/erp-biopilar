<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('system_stock', 15, 4);
            $table->decimal('physical_stock', 15, 4);
            $table->decimal('difference', 15, 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
