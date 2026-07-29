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
        Schema::table('report_phases', function (Blueprint $table) {
            $table->longText('opening_paragraph')->nullable()->after('progress_percentage');
            $table->longText('progress_paragraph')->nullable()->after('opening_paragraph');
            $table->longText('closing_paragraph')->nullable()->after('progress_paragraph');
            $table->longText('additional_notes')->nullable()->after('closing_paragraph');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_phases', function (Blueprint $table) {
            $table->dropColumn([
                'opening_paragraph',
                'progress_paragraph',
                'closing_paragraph',
                'additional_notes'
            ]);
        });
    }
};
