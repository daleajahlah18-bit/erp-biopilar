<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_productions', function (Blueprint $table) {
            $table->id();
            $table->string('project_production_number')->unique();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->date('production_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_productions');
    }
};
