<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            // Kembalikan seperti semula jika di-rollback
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });
    }
};
