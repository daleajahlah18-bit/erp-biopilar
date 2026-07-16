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
        Schema::create('sales_order_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->string('service_name');
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_invoice_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->onDelete('cascade');
            $table->string('service_name');
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
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
        Schema::dropIfExists('sales_invoice_services');
        Schema::dropIfExists('sales_order_services');
    }
};
