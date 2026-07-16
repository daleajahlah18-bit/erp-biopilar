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
        Schema::create('material_issue_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('products');
            $table->decimal('quantity_required', 15, 4);
            $table->decimal('quantity_issued', 15, 4);
            $table->foreignId('unit_id')->constrained('units');
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
        Schema::dropIfExists('material_issue_details');
    }
};
