<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Hapus prosedur lama jika ada
        DB::unprepared('DROP PROCEDURE IF EXISTS recalc_presensi_day');

        // Buat prosedur "kosong" — NO-OP.
        // Catatan: tanpa DELIMITER karena body kita kosong (tidak mengandung ;) sehingga aman di PDO.
        DB::unprepared("
            CREATE PROCEDURE recalc_presensi_day(IN p_karyawan_id INT, IN p_tanggal DATE)
            BEGIN
                -- NO-OP: seluruh logika sudah ditangani di layer PHP (Observer + Service).
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS recalc_presensi_day');
    }
};
