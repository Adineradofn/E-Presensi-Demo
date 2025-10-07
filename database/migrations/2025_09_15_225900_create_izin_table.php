<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('izin', function (Blueprint $table) {
            $table->bigIncrements('id_izin');                           // PK
            $table->unsignedBigInteger('id_karyawan');                  // FK -> karyawan

            $table->enum('jenis', ['izin','sakit']);                    // // tipe izin
            $table->date('tanggal_mulai');                              // // rentang tanggal
            $table->date('tanggal_akhir');
            $table->string('alasan', 255)->nullable();                  // // opsional
            $table->string('bukti_path', 255);                          // // path file privat
            $table->enum('status', ['pending','diterima','ditolak'])    // // status proses
                  ->default('pending');

            $table->timestamps();

            // // Jaga integritas dan akselerasi query
            $table->foreign('id_karyawan')
                  ->references('id_karyawan')->on('karyawan')
                  ->cascadeOnDelete();

            $table->index(['id_karyawan','tanggal_mulai','tanggal_akhir','status']);
            $table->index(['id_karyawan','created_at']);                // // bantu cek "1x per hari"
        });
    }

    public function down(): void {
        Schema::dropIfExists('izin');
    }
};
