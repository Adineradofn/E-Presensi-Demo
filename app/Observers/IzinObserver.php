<?php

namespace App\Observers;

use App\Models\Izin;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;

/**
 * IzinObserver
 * ------------
 * - Auto-approve: izin/sakit/cuti saat creating (default selain itu = pending).
 * - "Izin terlambat" wajib 1 hari.
 * - create/update/delete → recalc range (tanpa membuat baris presensi baru).
 *
 * Catatan: jika mengubah/hapus langsung di DB (bukan via Eloquent),
 * observer TIDAK terpanggil. Gunakan command recalc (lihat di bawah) bila perlu.
 */
class IzinObserver
{
    public function __construct(protected AttendanceService $svc) {}

    public function creating(Izin $izin): void
    {
        // Normalisasi tanggal
        $mulai   = $izin->tanggal_mulai instanceof Carbon ? $izin->tanggal_mulai : Carbon::parse($izin->tanggal_mulai, 'Asia/Makassar');
        $selesai = $izin->tanggal_selesai
            ? ($izin->tanggal_selesai instanceof Carbon ? $izin->tanggal_selesai : Carbon::parse($izin->tanggal_selesai, 'Asia/Makassar'))
            : $mulai->copy();

        if ($mulai->gt($selesai)) $selesai = $mulai->copy();
        if ($izin->jenis === 'izin terlambat') $selesai = $mulai->copy();

        // Simpan kembali (biarkan cast model yang urus)
        $izin->tanggal_mulai   = $mulai;
        $izin->tanggal_selesai = $selesai;

        // Auto-approve untuk jenis tertentu
        if (in_array($izin->jenis, ['izin','sakit','cuti'], true)) {
            $izin->status = 'disetujui';
        } else {
            $izin->status ??= 'pending';
        }
    }

    public function created(Izin $izin): void  { $this->recalcRange($izin); }
    public function updated(Izin $izin): void  { $this->recalcRange($izin); }
    public function deleted(Izin $izin): void  { $this->recalcRange($izin); }

    private function recalcRange(Izin $izin): void
    {
        $tz = config('app.timezone', 'Asia/Makassar');
        $this->svc->recalculateRange(
            $izin->karyawan_id,
            Carbon::parse($izin->tanggal_mulai, $tz),
            Carbon::parse($izin->tanggal_selesai, $tz),
        );
    }
}
