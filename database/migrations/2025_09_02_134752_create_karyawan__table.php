
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->bigIncrements('id_karyawan'); // Primary key auto increment
            $table->string('nip', 100)->unique(); // NIP unik
            $table->string('nik', 100);
            $table->string('nama', 100);
            $table->string('alamat', 100);
            $table->string('email', 100);
            $table->string('password', 100);
            $table->string('divisi', 100);
            $table->string('jabatan', 100);
            $table->string('foto', 100)->nullable();
            $table->enum('role', ['karyawan', 'admin'])->nullable(false); // wajib diisi
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
