<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use App\Models\Presensi;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function __construct(
        protected AttendanceService $svc
    ) {}

    private function currentKaryawanId(): ?int
    {
        $u = Auth::user();
        return $u?->id;
    }

    /**
     * Helper: ambil konfigurasi jam kerja untuk presensi hari ini.
     * Sumber: Presensi -> Jadwal -> JamKerja
     */
    private function resolveJamKerjaForToday(?Presensi $presensi, string $tz, string $tgl): ?array
    {
        $jk = $presensi?->jadwal?->jamKerja;
        if (!$jk) return null;

        // Waktu pokok
        $jamMasuk  = Carbon::parse($tgl.' '.$jk->jam_masuk,  $tz);
        $jamPulang = Carbon::parse($tgl.' '.$jk->jam_pulang, $tz);

        // Parameter gate (menit)
        $masukTutupSesudah   = (int)($jk->masuk_tutup_sesudah   ?? 60); // default 60 (sesuai kebutuhanmu)
        $pulangBukaSebelum   = (int)($jk->pulang_buka_sebelum   ?? 30); // default 30

        // Turunan:
        // - check-in "invalid tanpa izin" jika >= jam_masuk + masuk_tutup_sesudah
        // - check-in ditolak jika waktu sudah memasuki "jendela check-out" (>= jam_pulang - pulang_buka_sebelum)
        // - check-out dibuka pada (jam_pulang - pulang_buka_sebelum)
        $checkinClose  = $jamMasuk->copy()->addMinutes($masukTutupSesudah);
        $checkoutOpen  = $jamPulang->copy()->subMinutes($pulangBukaSebelum);

        return compact('jamMasuk', 'jamPulang', 'checkinClose', 'checkoutOpen');
    }

    /**
     * Check-In
     * Aturan:
     * - Blok jika ada izin tugas (disetujui).
     * - Tidak membuat baris presensi baru (wajib sudah disiapkan via prepare-today).
     * - Check-in DITOLAK jika waktu sudah memasuki jendela check-out (>= jam_pulang - pulang_buka_sebelum).
     * - Check-in >= (jam_masuk + masuk_tutup_sesudah) tanpa "izin terlambat" → simpan + INVALID.
     * - Selain itu → simpan + HADIR (nanti service akan evaluasi ulang saat needed).
     */
    public function checkIn(Request $r)
    {
        $tz  = config('app.timezone', 'Asia/Makassar');
        $now = Carbon::now($tz);
        $tgl = $now->toDateString();

        $idK = $this->currentKaryawanId(); abort_unless($idK, 403);

        $r->validate([
            'foto_masuk' => ['required','file','image','mimes:jpg,jpeg,png,heic,heif','max:10240'],
        ]);

        // Blok jika TUGAS aktif (disetujui)
        $tugas = Izin::where('karyawan_id', $idK)
            ->where('jenis', 'tugas')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tgl)
            ->whereDate('tanggal_selesai', '>=', $tgl)
            ->exists();
        if ($tugas) {
            return back()->with('error', 'Anda sedang masa tugas.');
        }

        // Ambil baris presensi hari ini — JANGAN BUAT KALAU TIDAK ADA
        $pres = Presensi::with('jadwal.jamKerja')
            ->where('karyawan_id', $idK)
            ->whereDate('tanggal', $tgl)
            ->first();

        if (!$pres) {
            return back()->with('error', 'Belum ada jadwal presensi hari ini. Coba lagi setelah sistem menyiapkan presensi.');
        }

        if ($pres->jam_masuk) {
            return back()->with('error', 'Anda sudah absen masuk.');
        }

        // Ambil konfigurasi jam kerja dari DB
        $jk = $this->resolveJamKerjaForToday($pres, $tz, $tgl);
        if (!$jk) {
            return back()->with('error', 'Konfigurasi jam kerja belum terpasang pada jadwal hari ini. Hubungi admin.');
        }

        // Waktu maksimal check-in: jika waktu sudah memasuki "jendela check-out", tolak
        if ($now->gte($jk['checkoutOpen'])) {
            return back()->with('error', 'Waktu absen masuk sudah lewat.');
        }

        // Late tanpa izin terlambat → INVALID
        $lateInvalid = false;
        if ($now->gte($jk['checkinClose'])) {
            $hasApprovedLate = Izin::where('karyawan_id', $idK)
                ->where('jenis', 'izin terlambat')
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '=', $tgl)
                ->whereDate('tanggal_selesai', '=', $tgl)
                ->exists();
            if (!$hasApprovedLate) {
                $lateInvalid = true;
            }
        }

        $path = $r->file('foto_masuk')->store('presensi_private/foto_masuk/'.$tgl, 'local');

        // Status saat check-in:
        // - normal → hadir
        // - terlambat tanpa izin → invalid
        $pres->update([
            'jam_masuk'       => $now,
            'ip_address'      => $r->ip(),
            'foto_masuk'      => $path,
            'status_presensi' => $lateInvalid ? 'invalid' : 'hadir',
        ]);

        // Sinkron (idempoten) — service akan evaluasi kembali sesuai izin & jam kerja
        $this->svc->recalculateDay($idK, $now);

        return back()->with('success', 'Absen masuk tersimpan.');
    }

    /**
     * Check-Out
     * Aturan:
     * - Blok jika ada izin tugas (disetujui).
     * - Check-out < (jam_pulang - pulang_buka_sebelum) → tolak (belum waktunya).
     * - Only check-out (tanpa check-in) → simpan + INVALID.
     * - Tidak membuat baris baru (wajib sudah disiapkan via prepare-today).
     *
     * NOTE: Jika check-in melewati batas (>= jam_masuk + masuk_tutup_sesudah) TANPA "izin terlambat",
     *       maka status TETAP invalid walaupun pulang normal (tidak dibalik jadi hadir).
     */
    public function checkOut(Request $r)
    {
        $tz  = config('app.timezone', 'Asia/Makassar');
        $now = Carbon::now($tz);
        $tgl = $now->toDateString();

        $idK = $this->currentKaryawanId(); abort_unless($idK, 403);

        $r->validate([
            'foto_pulang' => ['required','file','image','mimes:jpg,jpeg,png,heic,heif','max:10240'],
        ]);

        // Blok jika TUGAS aktif (disetujui)
        $tugas = Izin::where('karyawan_id', $idK)
            ->where('jenis', 'tugas')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tgl)
            ->whereDate('tanggal_selesai', '>=', $tgl)
            ->exists();
        if ($tugas) {
            return back()->with('error', 'Anda sedang masa tugas.');
        }

        // Ambil baris presensi hari ini — JANGAN BUAT KALAU TIDAK ADA
        $pres = Presensi::with('jadwal.jamKerja')
            ->where('karyawan_id', $idK)
            ->whereDate('tanggal', $tgl)
            ->first();

        if (!$pres) {
            return back()->with('error', 'Belum ada jadwal presensi hari ini. Coba lagi setelah sistem menyiapkan presensi.');
        }

        if ($pres->jam_pulang) {
            return back()->with('error', 'Anda sudah absen pulang.');
        }

        // Ambil konfigurasi jam kerja dari DB
        $jk = $this->resolveJamKerjaForToday($pres, $tz, $tgl);
        if (!$jk) {
            return back()->with('error', 'Konfigurasi jam kerja belum terpasang pada jadwal hari ini. Hubungi admin.');
        }

        // Waktu minimal check-out: < checkoutOpen → tolak
        if ($now->lt($jk['checkoutOpen'])) {
            return back()->with('error', 'Belum waktunya absen pulang.');
        }

        $path = $r->file('foto_pulang')->store('presensi_private/foto_pulang/'.$tgl, 'local');

        // Tentukan status:
        // - Tidak ada jam_masuk → INVALID (only-out)
        // - Ada jam_masuk → cek terlambat tanpa izin menggunakan jam kerja dari DB
        $status = 'invalid';
        if ($pres->jam_masuk) {
            $jamMasukAt = Carbon::parse($pres->jam_masuk, $tz);

            $hasApprovedLate = Izin::where('karyawan_id', $idK)
                ->where('jenis', 'izin terlambat')
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '=', $tgl)
                ->whereDate('tanggal_selesai', '=', $tgl)
                ->exists();

            $isLateWithoutIzin = $jamMasukAt->gte($jk['checkinClose']) && !$hasApprovedLate;
            $status = $isLateWithoutIzin ? 'invalid' : 'hadir';
        }

        $pres->update([
            'jam_pulang'       => $now,
            'ip_address'       => $r->ip(),
            'foto_pulang'      => $path,
            'status_presensi'  => $status,
        ]);

        // Sinkron (idempoten)
        $this->svc->recalculateDay($idK, $now);

        return back()->with('success', 'Absen pulang tersimpan.');
    }
}
