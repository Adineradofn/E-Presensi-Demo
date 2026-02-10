<?php

namespace App\Services;

use App\Models\Izin;
use App\Models\Presensi;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected string $tz = 'Asia/Makassar'
    ) {}

    /**
     * Helper: hitung batas check-in close (jam_masuk + masuk_tutup_sesudah) dari jadwal/jam_kerja.
     * Return: Carbon atau null kalau jam kerja tidak tersedia.
     */
    private function computeCheckinClose(?Presensi $presensi, IlluminateCarbon $date): ?IlluminateCarbon
    {
        $jk = $presensi?->jadwal?->jamKerja;
        if (!$jk) return null;

        $jamMasuk = IlluminateCarbon::parse($date->toDateString().' '.$jk->jam_masuk, $this->tz);
        $menit    = (int)($jk->masuk_tutup_sesudah ?? 60);
        return $jamMasuk->copy()->addMinutes($menit);
    }

    /** Recalculate satu hari (NO-CREATE). */
    public function recalculateDay(int $karyawanId, CarbonInterface $date): void
    {
        $date = IlluminateCarbon::instance($date)->timezone($this->tz)->startOfDay();

        DB::transaction(function () use ($karyawanId, $date) {
            $presensi = Presensi::with('jadwal.jamKerja')
                ->where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $date->toDateString())
                ->first();

            if (!$presensi) return;

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

            // 2) IZIN TERLAMBAT (approve) → butuh in+out lengkap
            if ($approvedIzinTerlambat) {
                $newStatus = ($jamMasuk && $jamPulang) ? 'hadir' : 'invalid';
                $newIzinId = $approvedIzinTerlambat->id;
                $this->applyIfChanged($presensi, $newStatus, $newIzinId);
                return;
            }

            // 3) IZIN/SAKIT/CUTI (approve)
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

            // 4) TANPA izin disetujui → fakta absen + CEK TERLAMBAT BERDASARKAN JAM_KERJA
            if ($jamMasuk && $jamPulang) {
                $checkinClose = $this->computeCheckinClose($presensi, $date);
                if ($checkinClose) {
                    $jamMasukAt = IlluminateCarbon::parse($jamMasuk, $this->tz);
                    $isLateWithoutIzin = $jamMasukAt->gte($checkinClose); // >= batas ⇒ terlambat tanpa izin
                    $newStatus = $isLateWithoutIzin ? 'invalid' : 'hadir';
                } else {
                    // Jika tidak ada jam kerja, fallback: jangan ubah (hindari hardcode)
                    $newStatus = $presensi->status_presensi;
                }
                $newIzinId = null;
            } elseif (!$jamMasuk && $jamPulang) {
                $newStatus = 'invalid';   // only out
                $newIzinId = null;
            } else {
                // none → alpa (cabut status hadir yang menempel)
                $newIzinId = null;
                if (!$jamMasuk && !$jamPulang) {
                    $newStatus = 'alpa';
                }
                // only-in: biarkan; finalize yang memutuskan di akhir hari
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
            $presensi->save();
        }
    }
}
