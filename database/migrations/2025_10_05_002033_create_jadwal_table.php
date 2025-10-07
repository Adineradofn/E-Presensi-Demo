<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id(); // standar: id

            // asumsi: tabel karyawan punya PK 'id'
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->cascadeOnDelete();

            $table->foreignId('jam_kerja_id')
                  ->constrained('jam_kerja'); // refer ke jam_kerja.id

            $table->date('tanggal');
            $table->boolean('libur')->default(false);
            $table->timestamps();

            // constraint & index mengikuti dump lama, tapi dengan nama kolom baru
            $table->unique(['karyawan_id', 'tanggal'], 'jadwal_karyawan_tanggal_unique');
            $table->index(['tanggal', 'jam_kerja_id'], 'jadwal_tanggal_jam_kerja_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
