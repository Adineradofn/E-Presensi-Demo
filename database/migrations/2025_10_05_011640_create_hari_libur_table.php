<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_hari_libur_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('hari_libur', function (Blueprint $table) {
      $table->id();
      $table->date('tanggal');           // satu tanggal
      $table->string('judul', 150);      // WAJIB (NOT NULL)
      $table->string('keterangan', 255)->nullable();
      $table->timestamps();

      $table->index('tanggal');
      // kalau ingin cegah duplikat tanggal: $table->unique('tanggal');
    });
  }
  public function down(): void {
    Schema::dropIfExists('hari_libur');
  }
};
