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
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code')->unique();
            $table->string('category_name');
            $table->integer('default_useful_life_commercial');
            $table->string('default_method_commercial'); // Straight Line, Double Declining Balance
            $table->integer('default_useful_life_fiscal');
            $table->string('default_method_fiscal'); 
            $table->decimal('default_residual_value_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            // General Information
            $table->string('asset_code')->unique();
            $table->string('asset_name');
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('restrict');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('asset_description')->nullable();
            
            // Location
            $table->string('location')->nullable(); // Simple text
            $table->string('department')->nullable(); // Simple text
            $table->string('responsible_person')->nullable(); // Simple text
            
            // Financial Information
            $table->date('purchase_date');
            $table->date('start_depreciation_date');
            $table->decimal('acquisition_cost', 20, 2);
            $table->decimal('residual_value', 20, 2)->default(0);
            
            // Commercial Config
            $table->string('commercial_method');
            $table->integer('commercial_useful_life');
            
            // Fiscal Config
            $table->string('fiscal_method');
            $table->integer('fiscal_useful_life');

            // Status & Other
            $table->string('status')->default('Active'); // Active, Under Maintenance, Idle, Sold, Disposed
            $table->string('vendor')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            
            // Attachments (URL or path)
            $table->string('purchase_invoice_doc')->nullable();
            $table->string('warranty_doc')->nullable();
            $table->string('photo_doc')->nullable();
            $table->string('manual_doc')->nullable();

            $table->timestamps();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('book_type'); // Commercial or Fiscal
            $table->string('period'); // Format: YYYY-MM
            $table->decimal('expense', 20, 2);
            $table->decimal('accumulated_depreciation', 20, 2);
            $table->decimal('book_value', 20, 2);
            $table->timestamps();
            
            // Prevent running depreciation twice for the same asset and book type in the same period
            $table->unique(['asset_id', 'book_type', 'period']);
        });

        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->date('maintenance_date');
            $table->string('maintenance_type'); // Preventive Maintenance, Corrective Maintenance, etc.
            $table->string('vendor')->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost', 20, 2)->default(0);
            $table->string('document_link')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_improvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->date('improvement_date');
            $table->decimal('improvement_cost', 20, 2);
            $table->text('description')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('vendor')->nullable();
            $table->decimal('previous_book_value_commercial', 20, 2);
            $table->decimal('new_book_value_commercial', 20, 2);
            $table->decimal('previous_book_value_fiscal', 20, 2);
            $table->decimal('new_book_value_fiscal', 20, 2);
            $table->timestamps();
        });

        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->date('movement_date');
            $table->string('from_department')->nullable();
            $table->string('to_department')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->string('from_pic')->nullable();
            $table->string('to_pic')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_improvements');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
