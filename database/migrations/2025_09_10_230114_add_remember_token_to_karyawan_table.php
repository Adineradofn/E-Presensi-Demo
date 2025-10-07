<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan', 'remember_token')) {
                $table->rememberToken()->after('password'); // // varchar(100) nullable
            }
        });
    }

    public function down(): void {
        Schema::table('karyawan', function (Blueprint $table) {
            if (Schema::hasColumn('karyawan', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};
