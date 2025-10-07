<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // supaya bisa login (guard default)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tabel & PK default
    protected $table = 'karyawan';

    // Kolom yang bisa diisi saat create/update
    protected $fillable = [
        'nik', 'nama', 'alamat', 'email', 'password',
        'divisi', 'jabatan', 'foto', 'role',
    ];

    // Sembunyikan field sensitif saat toArray()/toJson()
    protected $hidden = ['password', 'remember_token'];

    // Laravel 10+: otomatis hash saat set $model->password = 'plain'
    protected $casts = [
        'password' => 'hashed',
    ];

    // Accessor sederhana: ambil nama depan dari 'nama'
    public function getFirstNameAttribute(): string
    {
        $raw = $this->nama ?? ($this->name ?? '');
        $raw = trim(preg_replace('/\s+/u', ' ', $raw));
        if ($raw === '') return '';
        $parts = preg_split('/\s+/u', $raw, 2);
        return $parts[0] ?? '';
    }

    // Relasi: satu Karyawan punya banyak Jadwal
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'karyawan_id', 'id');
    }

    // Relasi: satu Karyawan punya banyak Presensi
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'karyawan_id', 'id');
    }

    // Relasi: satu Karyawan punya banyak Izin
    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class, 'karyawan_id', 'id');
    }
}
