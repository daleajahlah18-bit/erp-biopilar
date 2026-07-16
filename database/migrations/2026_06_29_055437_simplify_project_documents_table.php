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
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn([
                'google_drive_file_id',
                'google_drive_folder_id',
                'mime_type',
                'file_size'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->string('google_drive_file_id')->nullable();
            $table->string('google_drive_folder_id')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
        });
    }
};
