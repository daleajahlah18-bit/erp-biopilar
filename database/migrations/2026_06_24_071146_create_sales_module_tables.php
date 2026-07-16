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
        // 1. Customers Table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('customer_name');
            $table->string('customer_pic')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->integer('payment_terms')->default(0)->comment('In days');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Sales Orders Table
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('sales_order_date');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Confirmed', 'Invoiced', 'Cancelled'])->default('Draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Sales Order Details Table
        Schema::create('sales_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 4. Sales Invoices Table
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders');
            $table->date('invoice_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['Draft', 'Approved', 'Paid', 'Cancelled'])->default('Draft');
            $table->enum('payment_status', ['Unpaid', 'Partially Paid', 'Paid'])->default('Unpaid');
            $table->integer('terms_of_payment_days')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Sales Invoice Details Table
        Schema::create('sales_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 6. Sales Payments Table
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices');
            $table->date('payment_date');
            $table->decimal('payment_amount', 15, 2);
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_payments');
        Schema::dropIfExists('sales_invoice_details');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('sales_order_details');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('customers');
    }
};
