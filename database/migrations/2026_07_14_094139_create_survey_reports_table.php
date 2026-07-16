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
        Schema::create('survey_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->string('survey_location');
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('pic_client')->nullable();
            $table->string('phone_number')->nullable();
            $table->date('survey_date');
            $table->string('surveyor');
            $table->text('opening_description');
            $table->text('closing_description')->nullable();
            $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('survey_reports');
    }
};
