<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    // Nama tabel di DB (default plural Eloquent sudah sama, tulis eksplisit biar jelas)
    protected $table = 'izin';

    // PK default di skema sekarang adalah 'id' → tidak perlu $primaryKey
    // Tipe PK: BIGINT UNSIGNED AUTO_INCREMENT (lihat dump SQL)

    // Kolom yang diizinkan untuk mass assignment (create/update)
    protected $fillable = [
        'karyawan_id',        // FK ke karyawan.id
        'tanggal_pengajuan',  // date | bisa NULL
        'jenis',              // enum: izin/sakit/cuti/izin terlambat/tugas
        'tanggal_mulai',      // date
        'tanggal_selesai',    // date
        'alasan',             // string | nullable
        'bukti_path',         // string (path file bukti)
        'status',             // enum: pending/disetujui/ditolak
    ];

    // Casting tipe tanggal supaya otomatis jadi Carbon saat diakses
    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
    ];

    // Relasi: Izin dimiliki satu Karyawan
    public function karyawan(): BelongsTo
    {
        // belongsTo(Model, foreignKey di tabel ini, ownerKey di tabel tujuan)
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }
}
