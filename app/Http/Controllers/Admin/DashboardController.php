<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Controller untuk halaman Dashboard Admin.
 * - View utama hanya butuh judul.
 * - Angka/statistik dipolling via endpoint JSON (stats()) agar realtime.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard.
     * View akan melakukan fetch ke /dashboard/stats untuk mengambil angka terbaru.
     */
    public function index()
    {
        // Halaman hanya butuh title; angka diambil via fetch() (stats()) agar realtime.
        return view('admin.dashboard', [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * Endpoint JSON untuk statistik realtime dashboard.
     * GET /dashboard/stats
     *
     * Angka yang dikembalikan:
     * - hadir:         jumlah presensi status 'hadir' atau 'terlambat' di TANGGAL HARI INI
     * - terlambat:     jumlah presensi status 'terlambat' di hari ini
     * - tidak_hadir:   jumlah presensi status 'tidak hadir' di hari ini
     * - izin_pending:  total permohonan izin berstatus 'pending' (global, tidak dibatasi tanggal)
     *
     * Catatan:
     * - Tanggal hari ini ditentukan berdasarkan timezone aplikasi (default Asia/Makassar).
     * - Cocok dipanggil berkala dari Blade (polling) agar kartu statistik selalu up-to-date.
     */
    public function stats(Request $request)
    {
        // Gunakan timezone dari config; fallback ke Asia/Makassar
        $tz = config('app.timezone', 'Asia/Makassar');

        // Tentukan tanggal hari ini sesuai timezone (format Y-m-d)
        $today = Carbon::now($tz)->toDateString();

        // === Hitung metrik kehadiran hari ini ===

        // Kehadiran Hari Ini = status 'hadir' + 'terlambat'
        $hadirHariIni = Presensi::whereDate('tanggal', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        // Terlambat = status 'terlambat'
        $terlambat = Presensi::whereDate('tanggal', $today)
            ->where('status', 'terlambat')
            ->count();

        // Tidak Hadir = status 'tidak hadir'
        $tidakHadir = Presensi::whereDate('tanggal', $today)
            ->where('status', 'tidak hadir')
            ->count();

        // Izin Pending (global, tidak dibatasi tanggal)
        $izinPending = Izin::where('status', 'pending')->count();

        // Kembalikan response JSON untuk dikonsumsi oleh frontend (Blade/JS)
        return response()->json([
            'date'         => $today,        // tanggal acuan perhitungan
            'hadir'        => $hadirHariIni, // total hadir (termasuk terlambat)
            'terlambat'    => $terlambat,    // total terlambat
            'tidak_hadir'  => $tidakHadir,   // total tidak hadir
            'izin_pending' => $izinPending,  // total izin berstatus pending
        ]);
    }
}
