<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JamKerja extends Model
{
    // Tabel jam_kerja, PK default 'id'
    protected $table = 'jam_kerja';

    // Kolom-konfigurasi shift
    protected $fillable = [
        'nama',                 // mis: "Shift Pagi"
        'jam_masuk',            // time HH:MM:SS
        'jam_pulang',           // time HH:MM:SS
        'masuk_buka_sebelum',   // menit sebelum jam_masuk gate 'masuk' dibuka
        'masuk_tutup_sesudah',  // menit sesudah jam_masuk gate 'masuk' ditutup
        'pulang_buka_sebelum',  // menit sebelum jam_pulang gate 'pulang' dibuka
        'pulang_tutup_sesudah', // menit sesudah jam_pulang gate 'pulang' ditutup
    ];

    // Relasi: satu JamKerja dipakai oleh banyak Jadwal
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'jam_kerja_id', 'id');
    }
}
