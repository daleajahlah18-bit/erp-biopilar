<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('finance_expense_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_expense_id')->constrained('finance_expenses')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable(); // in KB or bytes
            $table->unsignedBigInteger('uploaded_by')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_expense_attachments');
    }
};
