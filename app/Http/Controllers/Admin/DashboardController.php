<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard Admin & Co-Admin
 * - View utama ringan; angka dipolling via endpoint JSON (stats()) agar realtime.
 */
class DashboardController extends Controller
{
    /** Halaman dashboard (Blade akan fetch ke stats()). */
    public function index()
    {
        return view('admin.dashboard', [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * Endpoint JSON statistik realtime.
     * GET /dashboard/stats
     *
     * Angka yang dikembalikan:
     * - hadir:        jumlah presensi status_presensi = 'hadir' di HARI INI
     * - absen:        jumlah presensi status_presensi IN ('izin','cuti','alpa','sakit') di HARI INI
     * - invalid:      jumlah presensi status_presensi = 'invalid' di HARI INI
     * - izin_pending: total pengajuan izin 'pending' sesuai ROLE:
     *                 • admin   -> hanya jenis = 'tugas'
     *                 • co-admin-> selain 'tugas'
     *                 • lainnya -> global (tanpa filter jenis)
     */
    public function stats(Request $request)
    {
        $tz    = config('app.timezone', 'Asia/Makassar');
        $today = Carbon::now($tz)->toDateString();

        // Kehadiran (hanya 'hadir')
        $hadir = Presensi::whereDate('tanggal', $today)
            ->where('status_presensi', 'hadir')
            ->count();

        // Tidak Hadir terklasifikasi: izin|cuti|alpa|sakit
        $absen = Presensi::whereDate('tanggal', $today)
            ->whereIn('status_presensi', ['izin', 'cuti', 'alpa', 'sakit'])
            ->count();

        // Invalid
        $invalid = Presensi::whereDate('tanggal', $today)
            ->where('status_presensi', 'invalid')
            ->count();

        // Izin pending (role-aware)
        $role = strtolower((string) (Auth::user()->role ?? ''));
        $izinQuery = Izin::query()->where('status', 'pending');

        if ($role === 'admin') {
            // Admin hanya melihat pengajuan TUGAS
            $izinQuery->where('jenis', 'tugas');
        } elseif ($role === 'co-admin') {
            // Co-admin hanya selain TUGAS
            $izinQuery->where('jenis', '!=', 'tugas');
        }
        $izinPending = $izinQuery->count();

        return response()->json([
            'date'         => $today,
            'hadir'        => $hadir,
            'absen'        => $absen,
            'invalid'      => $invalid,
            'izin_pending' => $izinPending,
        ]);
    }
}
