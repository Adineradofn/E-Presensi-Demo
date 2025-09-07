<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Karyawan extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'karyawan';

    // Primary key
    protected $primaryKey = 'id_karyawan';

    // Auto increment
    public $incrementing = true;

    // Tipe primary key
    protected $keyType = 'int';

    // Kolom yang bisa diisi (mass assignment)
    protected $fillable = [
        'nip',
        'nik',
        'nama',
        'alamat',
        'email',
        'password',
        'divisi',
        'jabatan',
        'foto',
        'role', // admin atau karyawan
    ];

    /**
     * Mutator untuk otomatis hash password
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }
}
