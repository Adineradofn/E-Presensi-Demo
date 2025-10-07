<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Karyawan;

class KaryawanSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        Karyawan::create([
            'nik'      => '123',
            'nama'     => 'Admin Sistem',
            'alamat'   => 'Jl. Merdeka No. 1',
            'email'    => 'admin@example.com',
            'password' => '123', // akan otomatis di-hash oleh mutator di Model
            'divisi'   => 'IT',
            'jabatan'  => 'Administrator',
            'foto'     => null,
            'role'     => 'admin',
        ]);
    }
}
