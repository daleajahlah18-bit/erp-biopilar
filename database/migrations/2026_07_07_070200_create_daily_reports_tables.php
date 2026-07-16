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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('report_number')->unique();
            $table->date('report_date');
            
            $table->string('weather')->nullable();
            $table->text('work_description')->nullable();
            $table->text('evaluation_notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('daily_report_manpower', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->string('position');
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('daily_report_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->string('material_name');
            $table->decimal('volume', 15, 2);
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_report_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->string('tool_name');
            $table->integer('quantity');
            $table->string('unit');
            $table->timestamps();
        });

        Schema::create('daily_report_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->string('photo');
            $table->string('caption')->nullable();
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
        Schema::dropIfExists('daily_report_documentations');
        Schema::dropIfExists('daily_report_tools');
        Schema::dropIfExists('daily_report_materials');
        Schema::dropIfExists('daily_report_manpower');
        Schema::dropIfExists('daily_reports');
    }
};
