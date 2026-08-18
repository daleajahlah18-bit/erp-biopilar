<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('finance_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('finance_expense_categories')->onDelete('restrict');
            $table->date('expense_date');
            $table->text('description');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method'); // Cash, Bank Transfer, Debit Card, Credit Card, E-Wallet, Other
            $table->string('paid_to')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('project_id');
            $table->index('category_id');
            $table->index('expense_date');
            $table->index('expense_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_expenses');
    }
};
