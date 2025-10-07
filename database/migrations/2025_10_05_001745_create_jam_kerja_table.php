<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jam_kerja', function (Blueprint $table) {
            $table->id(); // standar: id
            $table->string('nama', 100); // dari nama_jam_kerja -> nama
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->integer('masuk_buka_sebelum')->default(60);
            $table->integer('masuk_tutup_sesudah')->default(0);
            $table->integer('pulang_buka_sebelum')->default(0);
            $table->integer('pulang_tutup_sesudah')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_kerja');
    }
};
