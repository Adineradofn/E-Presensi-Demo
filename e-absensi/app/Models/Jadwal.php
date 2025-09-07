<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model {
  protected $table = 'jadwal';
  protected $primaryKey = 'id_jadwal';
  protected $fillable = ['id_karyawan','id_jam_kerja','tanggal','libur'];
  public function jamKerja(){ return $this->belongsTo(JamKerja::class,'id_jam_kerja'); }
}

