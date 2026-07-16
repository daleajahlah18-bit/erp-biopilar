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
        Schema::create('item_journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->enum('transaction_type', ['Stock In', 'Stock Out', 'Transfer In', 'Transfer Out', 'Adjustment']);
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity', 15, 4);
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->date('transaction_date');
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
        Schema::dropIfExists('item_journals');
    }
};
