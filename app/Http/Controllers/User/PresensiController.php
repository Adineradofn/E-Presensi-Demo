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
     * Check-In
     * Aturan:
     * - Blok jika ada izin tugas (disetujui).
     * - Check-in >= 15:30 → tolak (tidak simpan).
     * - Check-in >= 09:00 tanpa izin terlambat disetujui → simpan + status invalid.
     * - Check-in <  09:00 → simpan + status hadir.
     * - TIDAK MEMBUAT presensi baru: jika baris belum ada → tolak (nunggu jadwal).
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

        // Waktu maksimal check-in
        if ($now->gte($now->copy()->setTime(15,30,0))) {
            return back()->with('error', 'Waktu absen masuk sudah lewat.');
        }

        // Ambil baris presensi hari ini — JANGAN BUAT KALAU TIDAK ADA
        $pres = Presensi::where('karyawan_id', $idK)->whereDate('tanggal', $tgl)->first();
        if (!$pres) {
            return back()->with('error', 'Belum ada jadwal presensi hari ini. Silakan coba lagi setelah jadwal dibuat oleh sistem.');
        }

        if ($pres->jam_masuk) {
            return back()->with('error', 'Anda sudah absen masuk.');
        }

        // Late tanpa izin terlambat → invalid
        $lateInvalid = false;
        if ($now->gte($now->copy()->setTime(9,0,0))) {
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

        // Sinkron (idempoten)
        $this->svc->recalculateDay($idK, $now);

        return back()->with('success', 'Absen masuk tersimpan.');
    }

    /**
     * Check-Out
     * Aturan:
     * - Blok jika ada izin tugas (disetujui).
     * - Check-out < 15:30 → tolak (tidak simpan).
     * - Only check-out (tanpa check-in) → simpan + invalid (HANYA kalau baris presensi sudah ada).
     * - TIDAK MEMBUAT presensi baru: jika baris belum ada → tolak (nunggu jadwal).
     *
     * ⛏️ FIX: Jika check-in telat (>=09:00) TANPA "izin terlambat" disetujui,
     *         maka status TETAP invalid meskipun pulang normal (tidak dibalik jadi hadir).
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

        // Waktu minimal check-out
        if ($now->lt($now->copy()->setTime(15,30,0))) {
            return back()->with('error', 'Belum waktunya absen pulang.');
        }

        // Ambil baris presensi hari ini — JANGAN BUAT KALAU TIDAK ADA
        $pres = Presensi::where('karyawan_id', $idK)->whereDate('tanggal', $tgl)->first();
        if (!$pres) {
            return back()->with('error', 'Belum ada jadwal presensi hari ini. Silakan coba lagi setelah jadwal dibuat oleh sistem.');
        }

        if ($pres->jam_pulang) {
            return back()->with('error', 'Anda sudah absen pulang.');
        }

        $path = $r->file('foto_pulang')->store('presensi_private/foto_pulang/'.$tgl, 'local');

        // ⛏️ FIX: tentukan status checkout dengan mempertahankan "invalid karena telat tanpa izin".
        // - Jika tidak ada jam_masuk → selalu invalid (only-out).
        // - Jika ada jam_masuk:
        //     * hitung apakah jam_masuk >= 09:00 dan TIDAK ada izin terlambat disetujui → invalid.
        //     * selain itu → hadir.
        $status = 'invalid';
        if ($pres->jam_masuk) {
            $jamMasukAt = Carbon::parse($pres->jam_masuk, $tz);
            $lateCutoff = Carbon::parse($tgl . ' 09:00:00', $tz);

            $hasApprovedLate = Izin::where('karyawan_id', $idK)
                ->where('jenis', 'izin terlambat')
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '=', $tgl)
                ->whereDate('tanggal_selesai', '=', $tgl)
                ->exists();

            $isLateWithoutIzin = $jamMasukAt->gte($lateCutoff) && !$hasApprovedLate;

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
