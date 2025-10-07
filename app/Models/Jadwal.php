<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Jadwal extends Model
{
    // Nama tabel eksplisit
    protected $table = 'jadwal';

    // Kolom yang boleh diisi mass assignment
    protected $fillable = [
        'karyawan_id',  // FK → karyawan.id
        'jam_kerja_id', // FK → jam_kerja.id
        'tanggal',      // date
        'libur',        // tinyint(1) → flag libur (0/1)
    ];

    // Cast agar field pas dipakai di kode
    protected $casts = [
        'tanggal' => 'date',
        'libur'   => 'boolean', // tinyint(1) → bool true/false
    ];

    // Relasi: banyak Jadwal milik satu JamKerja (shift)
    public function jamKerja(): BelongsTo
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'id');
    }

    // Relasi: banyak Jadwal milik satu Karyawan
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    // Relasi: satu Jadwal punya satu Presensi (pada hari yang sama)
    public function presensi(): HasOne
    {
        // hasOne(Model, foreignKey di tabel presensi, localKey di tabel ini)
        return $this->hasOne(Presensi::class, 'jadwal_id', 'id');
    }
}
