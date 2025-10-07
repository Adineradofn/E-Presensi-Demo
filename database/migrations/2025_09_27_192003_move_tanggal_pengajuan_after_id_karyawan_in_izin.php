<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pindahkan posisi kolom tanpa mengubah tipe/nullability
        DB::statement('ALTER TABLE `izin` MODIFY `tanggal_pengajuan` DATE NULL AFTER `id_karyawan`');
    }

    public function down(): void
    {
        // Kembalikan ke posisi sebelumnya (sesuaikan dengan posisi lama Anda)
        DB::statement('ALTER TABLE `izin` MODIFY `tanggal_pengajuan` DATE NULL AFTER `id_izin`');
        // Atau: AFTER `id` jika primary key Anda bernama `id`
        // DB::statement('ALTER TABLE `izin` MODIFY `tanggal_pengajuan` DATE NULL AFTER `id`');
    }
};
