<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 🎯 Hapus trigger lama bila ada (ganti dengan nama trigger asli milik Anda).
        // Cek dulu: SHOW TRIGGERS LIKE 'izin';  SHOW TRIGGERS LIKE 'presensi';
        // Lalu taruh nama yang benar di sini.
        DB::unprepared('DROP TRIGGER IF EXISTS trg_izin_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_izin_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_izin_after_delete');

        DB::unprepared('DROP TRIGGER IF EXISTS trg_presensi_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_presensi_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_presensi_after_delete');

        // Sekalian matikan event/cron DB kalau ada (opsional):
        // DB::unprepared("DROP EVENT IF EXISTS ev_finalize_today");
        // DB::unprepared("DROP EVENT IF EXISTS ev_prepare_today");
    }

    public function down(): void
    {
        // Tidak membuat kembali trigger lama (sengaja). Biarkan kosong / no-op.
    }
};
