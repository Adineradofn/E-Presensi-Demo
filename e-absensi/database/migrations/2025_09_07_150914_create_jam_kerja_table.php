<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jam_kerja', function (Blueprint $table) {
            $table->bigIncrements('id_jam_kerja');
            $table->string('nama_jam_kerja', 100);

            $table->time('jam_masuk');
            $table->time('jam_pulang');

            // Window buka/tutup absen (menit)
            $table->integer('masuk_buka_sebelum')->default(0);
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
