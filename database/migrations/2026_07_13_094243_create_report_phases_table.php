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
        Schema::create('report_phases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('report_number');
            $table->date('document_date');
            $table->decimal('progress_percentage', 5, 2);
            $table->string('client_sign_name_1')->nullable();
            $table->string('client_sign_position_1')->nullable();
            $table->string('client_sign_name_2')->nullable();
            $table->string('client_sign_position_2')->nullable();
            $table->string('company_sign_name_1')->nullable();
            $table->string('company_sign_position_1')->nullable();
            $table->string('company_sign_name_2')->nullable();
            $table->string('company_sign_position_2')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_phases');
    }
};
