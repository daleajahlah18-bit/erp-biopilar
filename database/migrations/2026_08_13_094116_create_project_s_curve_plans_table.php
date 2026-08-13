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
        Schema::create('project_s_curve_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('s_curve_item_id')->constrained('project_s_curve_items')->onDelete('cascade');
            $table->integer('week_number');
            $table->decimal('planned_percentage', 5, 2)->default(0);
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
        Schema::dropIfExists('project_s_curve_plans');
    }
};
