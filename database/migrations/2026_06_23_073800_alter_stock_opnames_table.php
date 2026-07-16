<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'system_stock', 'physical_stock', 'difference']);
            $table->string('opname_number')->after('id')->nullable();
            $table->foreignId('created_by')->after('notes')->nullable()->constrained('users');
        });
    }

    public function down()
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->decimal('system_stock', 15, 4)->default(0);
            $table->decimal('physical_stock', 15, 4)->default(0);
            $table->decimal('difference', 15, 4)->default(0);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['opname_number', 'created_by']);
        });
    }
};
