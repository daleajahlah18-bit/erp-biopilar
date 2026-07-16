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
        // 1. Alter goods_receipts
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->after('receipt_date');
            $table->string('payment_status')->default('Unpaid')->after('total_amount'); // Unpaid, Partially Paid, Paid
            $table->decimal('total_paid', 15, 2)->default(0)->after('payment_status');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('total_paid');
            $table->integer('terms_of_payment_days')->default(0)->after('remaining_amount');
            $table->date('due_date')->nullable()->after('terms_of_payment_days');
        });

        // 2. Drop and recreate purchase_payments
        Schema::dropIfExists('purchase_payments');
        
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('payment_amount', 15, 2);
            $table->string('payment_method'); // Transfer Bank, Cash, Giro, Lainnya
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('purchase_payments');

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'payment_status',
                'total_paid',
                'remaining_amount',
                'terms_of_payment_days',
                'due_date'
            ]);
        });
    }
};
