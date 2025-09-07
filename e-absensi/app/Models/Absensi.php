<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model {
  protected $table = 'absensi';
  protected $primaryKey = 'id_absensi';
  protected $fillable = ['id_karyawan','id_jadwal','tanggal','jam_masuk','jam_pulang','status','foto_masuk','foto_pulang','ip_address','lat','lng'];
  protected $casts = ['tanggal'=>'date','jam_masuk'=>'datetime','jam_pulang'=>'datetime'];
}

