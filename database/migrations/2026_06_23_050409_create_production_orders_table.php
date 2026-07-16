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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('production_number')->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('bill_of_material_id')->constrained('bill_of_materials');
            $table->decimal('quantity_target', 15, 4);
            $table->date('production_date');
            $table->string('person_in_charge');
            $table->enum('status', ['Draft','Released','In Progress','Finished','Cancelled'])->default('Draft');
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
        Schema::dropIfExists('production_orders');
    }
};
