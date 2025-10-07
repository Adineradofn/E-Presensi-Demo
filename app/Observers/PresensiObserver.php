<?php

namespace App\Observers;

use App\Models\Presensi;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;

/**
 * PresensiObserver
 * ----------------
 * - Saat baris presensi BARU dibuat (oleh prepare/scheduler) → langsung sinkron dgn izin aktif.
 * - Saat JAM berubah (jam_masuk/jam_pulang) → recalc ulang.
 *   *Tidak* memicu recalc saat status/izin_id berubah, karena itu justru hasil dari service.
 */
class PresensiObserver
{
    public function __construct(protected AttendanceService $svc) {}

    public function created(Presensi $presensi): void
    {
        $date = Carbon::parse($presensi->tanggal, config('app.timezone', 'Asia/Makassar'));
        $this->svc->recalculateDay($presensi->karyawan_id, $date);
    }

    public function updated(Presensi $presensi): void
    {
        if ($presensi->wasChanged(['jam_masuk', 'jam_pulang'])) {
            $date = Carbon::parse($presensi->tanggal, config('app.timezone', 'Asia/Makassar'));
            $this->svc->recalculateDay($presensi->karyawan_id, $date);
        }
    }
}
