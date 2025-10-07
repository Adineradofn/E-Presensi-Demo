<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('presensi', function (Blueprint $table) {
      $table->bigIncrements('id_presensi');             // PK
      $table->unsignedBigInteger('id_karyawan');       // FK ke karyawan
      $table->unsignedBigInteger('id_jadwal')->nullable(); // FK ke jadwal (nullable)

      $table->date('tanggal');                         // anchor tanggal kerja
      $table->timestamp('jam_masuk')->nullable();      // real check-in
      $table->timestamp('jam_pulang')->nullable();     // real check-out

      // Status utama
      $table->enum('status', ['hadir','terlambat','tidak hadir','izin','sakit'])
            ->default('tidak hadir');

      // Catatan tambahan (mis: "Tidak absen pulang")
      $table->string('catatan', 255)->nullable();

      // Bukti & metadata
      $table->string('foto_masuk', 255)->nullable();   // path foto check-in
      $table->string('foto_pulang', 255)->nullable();  // path foto check-out
      $table->string('ip_address', 64)->nullable();
      $table->decimal('lat', 10, 7)->nullable();
      $table->decimal('lng', 10, 7)->nullable();

      $table->timestamps();

      // Relasi & indeks
      $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->cascadeOnDelete();
      $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal')->nullOnDelete();

      $table->unique(['id_karyawan','tanggal']);       // 1 record/hari/karyawan
      $table->index(['tanggal','status']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('presensi');
  }
};
