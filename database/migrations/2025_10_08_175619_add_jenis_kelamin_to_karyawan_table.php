<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Tambahkan setelah 'jabatan' agar rapi. Nullable supaya data lama aman.
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan'])
                  ->nullable()
                  ->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn('jenis_kelamin');
        });
    }
};
