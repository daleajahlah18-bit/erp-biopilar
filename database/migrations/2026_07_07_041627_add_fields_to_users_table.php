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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('email');
            $table->string('photo')->nullable()->after('employee_id');
            $table->string('department')->nullable()->after('photo');
            $table->string('position')->nullable()->after('department');
            $table->string('phone_number')->nullable()->after('position');
            $table->enum('status', ['Active', 'Inactive', 'Suspended', 'Locked'])->default('Active')->after('phone_number');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'photo',
                'department',
                'position',
                'phone_number',
                'status',
                'last_login_at',
                'last_login_ip'
            ]);
        });
    }
};
