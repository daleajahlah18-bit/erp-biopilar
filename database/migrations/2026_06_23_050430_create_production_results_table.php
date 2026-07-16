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
        Schema::create('production_results', function (Blueprint $table) {
            $table->id();
            $table->string('result_number')->unique();
            $table->foreignId('production_order_id')->constrained('production_orders');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->date('result_date');
            $table->decimal('quantity_target', 15, 4);
            $table->decimal('quantity_finished', 15, 4);
            $table->decimal('quantity_reject', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_results');
    }
};
