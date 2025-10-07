<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JamKerja;

/**
 * Seeder JamKerja
 * Menggunakan kolom 'nama' (bukan nama_jam_kerja)
 * Jam kerja seragam Senin–Jumat 08:00–16:00
 */
class JamKerjaSeeder extends Seeder
{
    public function run(): void
    {
        JamKerja::firstOrCreate(
            ['nama' => 'Default 08-16'],
            [
                'jam_masuk' => '08:00:00',
                'jam_pulang'=> '16:00:00',
                'masuk_buka_sebelum' => 0,
                'masuk_tutup_sesudah'=> 0,
                'pulang_buka_sebelum'=> 0,
                'pulang_tutup_sesudah'=> 0,
            ]
        );
    }
}
