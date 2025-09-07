<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model {
  protected $table = 'jam_kerja';
  protected $primaryKey = 'id_jam_kerja';
  protected $fillable = [
    'nama_jam_kerja','jam_masuk','jam_pulang',
    'masuk_buka_sebelum','masuk_tutup_sesudah',
    'pulang_buka_sebelum','pulang_tutup_sesudah'
  ];
}

