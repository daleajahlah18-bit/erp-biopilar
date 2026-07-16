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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->decimal('total_purchase', 18, 2);
            $table->decimal('total_paid', 18, 2)->default(0);
            $table->decimal('remaining_payment', 18, 2)->default(0);
            $table->enum('payment_status', ['Belum Dibayar', 'Setengah Dibayar', 'Lunas'])->default('Belum Dibayar');
            $table->date('payment_date')->nullable();
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
        Schema::dropIfExists('purchase_payments');
    }
};
