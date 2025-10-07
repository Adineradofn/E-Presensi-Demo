<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('jam_kerja', function (Blueprint $table) {
      $table->bigIncrements('id_jam_kerja');       // PK
      $table->string('nama_jam_kerja', 100);       // "Senin-Kamis 08-16" / "Jumat 08-15"
      $table->time('jam_masuk');                   // 08:00
      $table->time('jam_pulang');                  // 16:00 / 15:00

      // Window (menit)
      $table->integer('masuk_buka_sebelum')->default(60); // buka 1 jam sebelum jam_masuk
      $table->integer('masuk_tutup_sesudah')->default(0); // (kita tutup di jam_pulang)
      $table->integer('pulang_buka_sebelum')->default(0); // buka pas jam_pulang
      $table->integer('pulang_tutup_sesudah')->default(0);// (kita tutup 23:59:59)

      $table->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('jam_kerja');
  }
};
