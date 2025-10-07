<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            // Kolom sudah ada dan bertipe unsignedBigInteger di DB Anda,
            // jadi tidak perlu change() / DBAL.

            // FK ke karyawan (wajib ada, kolom NOT NULL)
            $table->foreign('karyawan_id', 'presensi_karyawan_id_foreign')
                  ->references('id')->on('karyawan')   // ganti ke 'karyawans' jika itu nama tabelnya
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();                // cegah hapus karyawan kalau masih dipakai

            // FK ke jadwal (kolom nullable → nullOnDelete cocok)
            $table->foreign('jadwal_id', 'presensi_jadwal_id_foreign')
                  ->references('id')->on('jadwal')     // ganti ke 'jadwals' jika perlu
                  ->cascadeOnUpdate()
                  ->nullOnDelete();                    // kalau jadwal dihapus → set NULL
        });
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            // Drop constraint pakai nama yang kita set di atas:
            $table->dropForeign('presensi_karyawan_id_foreign');
            $table->dropForeign('presensi_jadwal_id_foreign');
        });
    }
};
