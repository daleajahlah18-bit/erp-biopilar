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
        Schema::create('project_s_curve_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('s_curve_id')->constrained('project_s_curves')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('project_s_curve_items')->onDelete('cascade');
            $table->string('work_code')->nullable();
            $table->string('work_name');
            $table->decimal('weight_percentage', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
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
        Schema::dropIfExists('project_s_curve_items');
    }
};
