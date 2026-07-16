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
        Schema::create('survey_report_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_report_id')->constrained('survey_reports')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('survey_report_nodes')->onDelete('cascade');
            $table->string('title');
            $table->string('node_type'); // category, group, item
            $table->string('qty')->nullable();
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('survey_report_nodes');
    }
};
