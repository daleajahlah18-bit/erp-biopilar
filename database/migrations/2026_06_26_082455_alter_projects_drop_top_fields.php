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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'terms_of_payment_value',
                'terms_of_payment_type',
                'sales_order_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('terms_of_payment_value')->nullable();
            $table->enum('terms_of_payment_type', ['DAY', 'MONTH'])->nullable();
            $table->unsignedBigInteger('sales_order_id')->nullable();
        });
    }
};
