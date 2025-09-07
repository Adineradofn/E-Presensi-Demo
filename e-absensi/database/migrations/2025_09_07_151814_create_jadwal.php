<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->bigIncrements('id_jadwal');

            $table->unsignedBigInteger('id_karyawan');
            $table->unsignedBigInteger('id_jam_kerja'); // <-- ganti

            $table->date('tanggal');
            $table->boolean('libur')->default(false);

            $table->timestamps();

            // FK
            $table->foreign('id_karyawan')
                  ->references('id_karyawan')->on('karyawan')
                  ->onDelete('cascade');

            $table->foreign('id_jam_kerja') // <-- ganti
                  ->references('id_jam_kerja')->on('jam_kerja') // <-- ganti
                  ->onDelete('cascade');

            $table->unique(['id_karyawan', 'tanggal']);
            $table->index(['tanggal', 'id_jam_kerja']); // <-- ganti
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
