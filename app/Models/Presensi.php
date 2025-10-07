<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    // Nama tabel
    protected $table = 'presensi';

    // Kolom yang boleh diisi
    protected $fillable = [
        'karyawan_id',      // FK → karyawan.id (NOT NULL)
        'jadwal_id',        // FK → jadwal.id (NULLABLE; dihapus jadwal → SET NULL)
        'izin_id',          // FK → izin.id (NULLABLE; dihapus izin → SET NULL)
        'tanggal',          // date (unik per karyawan)
        'jam_masuk',        // timestamp | nullable
        'jam_pulang',       // timestamp | nullable
        'status_presensi',  // enum: hadir/izin/sakit/cuti/invalid/alpa (default: invalid)
        'catatan',          // varchar(255) | nullable
        'foto_masuk',       // path foto masuk | nullable
        'foto_pulang',      // path foto pulang | nullable
        'ip_address',       // ip saat presensi | nullable
    ];

    // Cast agar enak dipakai
    protected $casts = [
        'tanggal'    => 'date',
        'jam_masuk'  => 'datetime',
        'jam_pulang' => 'datetime',
    ];

    // Relasi: Presensi dimiliki satu Karyawan
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    // Relasi: Presensi terkait ke satu Jadwal (opsional)
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id');
    }

    // Relasi: Presensi bisa terkait satu Izin (opsional)
    public function izin(): BelongsTo
    {
        return $this->belongsTo(Izin::class, 'izin_id', 'id');
    }
}
