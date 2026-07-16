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
            $table->string('client_name')->nullable()->after('project_name');
            $table->date('client_po_date')->nullable()->after('person_in_charge');
            $table->decimal('po_amount', 18, 2)->nullable()->after('client_po_date');
            $table->decimal('hpp', 18, 2)->default(0)->after('po_amount');
            $table->enum('project_status', ['Draft', 'On Going', 'Completed', 'Cancelled'])->default('Draft')->after('hpp');
            $table->date('project_start_date')->nullable()->after('project_status');
            $table->date('project_end_date')->nullable()->after('project_start_date');
            $table->integer('terms_of_payment_value')->nullable()->after('project_end_date');
            $table->enum('terms_of_payment_type', ['DAY', 'MONTH'])->nullable()->after('terms_of_payment_value');
            $table->unsignedBigInteger('sales_order_id')->nullable()->after('terms_of_payment_type');
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
            $table->dropColumn([
                'client_name',
                'client_po_date',
                'po_amount',
                'hpp',
                'project_status',
                'project_start_date',
                'project_end_date',
                'terms_of_payment_value',
                'terms_of_payment_type',
                'sales_order_id'
            ]);
        });
    }
};
