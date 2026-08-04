<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('rab_name');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rab_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('rab_nodes')->cascadeOnDelete();
            $table->string('node_type'); // Section, Group, Item
            $table->string('title'); // Work Description
            $table->string('specification')->nullable();
            $table->decimal('qty', 15, 2)->nullable();
            $table->string('unit')->nullable(); // Free text unit
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_nodes');
        Schema::dropIfExists('rabs');
    }
};
