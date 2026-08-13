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
        Schema::create('project_s_curve_actuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('s_curve_id')->constrained('project_s_curves')->onDelete('cascade');
            $table->foreignId('s_curve_item_id')->constrained('project_s_curve_items')->onDelete('cascade');
            $table->integer('week_number');
            $table->decimal('actual_percentage', 5, 2)->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('project_s_curve_actuals');
    }
};
