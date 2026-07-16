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
        Schema::create('project_production_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_production_id')->constrained('project_productions')->cascadeOnDelete();
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
        Schema::dropIfExists('project_production_services');
    }
};
