<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->bigIncrements('id_absensi');

            $table->unsignedBigInteger('id_karyawan');
            // Disarankan: link ke jadwal (boleh nullable kalau belum ada jadwal)
            $table->unsignedBigInteger('id_jadwal')->nullable();

            // Anchor hari kerja (mirror dari jadwal.tanggal)
            $table->date('tanggal');

            // Waktu realisasi
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_pulang')->nullable();

            $table->enum('status', ['hadir','terlambat','tidak hadir','izin', 'sakit'])
                  ->default('tidak hadir');

            // Bukti opsional
            $table->string('foto_masuk', 255)->nullable();
            $table->string('foto_pulang', 255)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->timestamps();

            // FK
            $table->foreign('id_karyawan')
                  ->references('id_karyawan')->on('karyawan')
                  ->onDelete('cascade');

            $table->foreign('id_jadwal')
                  ->references('id_jadwal')->on('jadwal')
                  ->nullOnDelete();

            // Satu absensi per karyawan per hari kerja
            $table->unique(['id_karyawan', 'tanggal']);

            $table->index(['tanggal', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
