<?php

namespace App\Services;

use App\Models\Izin;
use App\Models\Presensi;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Support\Facades\DB;

/**
 * AttendanceService
 * -----------------
 * Prinsip: GANTI, BUKAN TAMBAH.
 * - NO CREATE row presensi.
 * - Update baris yang SUDAH ada.
 */
class AttendanceService
{
    public function __construct(
        protected string $tz = 'Asia/Makassar'
    ) {}

    /** Recalculate satu hari (NO-CREATE). */
    public function recalculateDay(int $karyawanId, CarbonInterface $date): void
    {
        $date = IlluminateCarbon::instance($date)->timezone($this->tz)->startOfDay();

        DB::transaction(function () use ($karyawanId, $date) {
            $presensi = Presensi::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $date->toDateString())
                ->first();

            if (!$presensi) return; // nunggu scheduler/prepare

            // Ambil izin overlap (rentang inklusif)
            $izinList = Izin::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal_mulai', '<=', $date->toDateString())
                ->whereDate('tanggal_selesai', '>=', $date->toDateString())
                ->get();

            $findApproved = fn(string $jenis) =>
                $izinList->first(fn($z) => $z->jenis === $jenis && $z->status === 'disetujui');

            $approvedTugas         = $findApproved('tugas');
            $approvedIzinTerlambat = $findApproved('izin terlambat');
            $approvedIzin          = $findApproved('izin');
            $approvedSakit         = $findApproved('sakit');
            $approvedCuti          = $findApproved('cuti');

            $jamMasuk  = $presensi->jam_masuk;
            $jamPulang = $presensi->jam_pulang;

            $newStatus = $presensi->status_presensi;
            $newIzinId = $presensi->izin_id;

            // 1) TUGAS → hadir (meski tanpa absen)
            if ($approvedTugas) {
                $newStatus = 'hadir';
                $newIzinId = $approvedTugas->id;
                $this->applyIfChanged($presensi, $newStatus, $newIzinId);
                return;
            }

            // 2) IZIN TERLAMBAT → wajib in+out lengkap; selain itu invalid
            if ($approvedIzinTerlambat) {
                $newStatus = ($jamMasuk && $jamPulang) ? 'hadir' : 'invalid';
                $newIzinId = $approvedIzinTerlambat->id;
                $this->applyIfChanged($presensi, $newStatus, $newIzinId);
                return;
            }

            // 3) IZIN/SAKIT/CUTI (disetujui)
            if ($approvedIzin || $approvedSakit || $approvedCuti) {
                $izinAktif = $approvedIzin ?: ($approvedSakit ?: $approvedCuti);

                if ($jamMasuk && $jamPulang) {
                    $newStatus = 'hadir';
                } elseif (($jamMasuk && !$jamPulang) || (!$jamMasuk && $jamPulang)) {
                    $newStatus = 'invalid';
                } else {
                    $newStatus = $izinAktif->jenis; // izin|sakit|cuti
                }

                $newIzinId = $izinAktif->id;
                $this->applyIfChanged($presensi, $newStatus, $newIzinId);
                return;
            }

            // 4) TANPA izin disetujui → fakta absen + CEK KETERLAMBATAN
            // ⛏️ FIX: bila in+out lengkap namun jam_masuk >= 09:00 dan
            //         tidak ada "izin terlambat" → INVALID (bukan hadir).
            if ($jamMasuk && $jamPulang) {
                $jamMasukAt = IlluminateCarbon::parse($jamMasuk, $this->tz);
                $lateCutoff = $date->copy()->setTime(9, 0, 0);

                // tidak ada $approvedIzinTerlambat karena sudah keluar di atas
                $isLateWithoutIzin = $jamMasukAt->gte($lateCutoff);
                $newStatus = $isLateWithoutIzin ? 'invalid' : 'hadir';
                $newIzinId = null;
            } elseif (!$jamMasuk && $jamPulang) {
                $newStatus = 'invalid';   // only out
                $newIzinId = null;
            } else {
                // only-in → biarkan; finalize yang akan memutuskan
                // none    → paksa alpa, supaya tidak “nempel hadir”
                $newIzinId = null;
                if (!$jamMasuk && !$jamPulang) {
                    $newStatus = 'alpa';
                }
            }

            $this->applyIfChanged($presensi, $newStatus, $newIzinId);
        });
    }

    /** Recalculate range inklusif (NO-CREATE). */
    public function recalculateRange(int $karyawanId, CarbonInterface $start, CarbonInterface $end): void
    {
        $start = IlluminateCarbon::instance($start)->timezone($this->tz)->startOfDay();
        $end   = IlluminateCarbon::instance($end)->timezone($this->tz)->startOfDay();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $this->recalculateDay($karyawanId, $d);
        }
    }

    /** Simpan hanya bila betul berubah. */
    private function applyIfChanged(Presensi $presensi, ?string $statusBaru, ?int $izinIdBaru): void
    {
        $dirty = false;

        if ($statusBaru !== null && $presensi->status_presensi !== $statusBaru) {
            $presensi->status_presensi = $statusBaru;
            $dirty = true;
        }
        if ($presensi->izin_id !== $izinIdBaru) {
            $presensi->izin_id = $izinIdBaru;
            $dirty = true;
        }

        if ($dirty) {
            $presensi->save(); // akan memicu observer updated (jam) hanya jika jam berubah
        }
    }
}
