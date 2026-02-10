<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya untuk MySQL yang menggunakan ENUM pada kolom 'role'
        DB::statement("ALTER TABLE `karyawan` MODIFY `role` ENUM('admin','co-admin','karyawan') NOT NULL");
    }

    public function down(): void
    {
        // Pastikan tidak ada data 'co-admin' sebelum rollback.
        DB::statement("UPDATE `karyawan` SET `role`='karyawan' WHERE `role`='co-admin'");
        DB::statement("ALTER TABLE `karyawan` MODIFY `role` ENUM('admin','karyawan') NOT NULL");
    }
};
