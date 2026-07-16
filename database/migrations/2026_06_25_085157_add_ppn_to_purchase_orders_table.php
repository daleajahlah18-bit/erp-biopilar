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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('is_ppn')->default(false)->after('total_amount');
            $table->decimal('ppn_percentage', 5, 2)->default(11)->after('is_ppn');
            $table->decimal('ppn_amount', 20, 2)->default(0)->after('ppn_percentage');
            $table->decimal('grand_total', 20, 2)->default(0)->after('ppn_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['is_ppn', 'ppn_percentage', 'ppn_amount', 'grand_total']);
        });
    }
};
