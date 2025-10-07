<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('jadwal', function (Blueprint $table) {
      $table->bigIncrements('id_jadwal');         // PK
      $table->unsignedBigInteger('id_karyawan');  // FK ke karyawan
      $table->unsignedBigInteger('id_jam_kerja'); // FK ke jam_kerja
      $table->date('tanggal');                    // hari kerja
      $table->boolean('libur')->default(false);   // sabtu/minggu libur
      $table->timestamps();

      $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->cascadeOnDelete();
      $table->foreign('id_jam_kerja')->references('id_jam_kerja')->on('jam_kerja')->restrictOnDelete();

      $table->unique(['id_karyawan','tanggal']);  // 1 jadwal per hari per karyawan
      $table->index(['tanggal','id_jam_kerja']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('jadwal');
  }
};
