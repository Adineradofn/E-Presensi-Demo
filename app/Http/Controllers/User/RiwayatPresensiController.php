<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RiwayatPresensiController extends Controller
{
    public function index(Request $request)
    {
        // Jika yang login adalah Karyawan (Authenticatable = Karyawan), id karyawan = Auth::id()
        $idKaryawan = Auth::id(); // <-- ini kuncinya

        abort_unless($idKaryawan, 403, 'Akun ini belum terhubung ke data karyawan.');

        $tz = config('app.timezone', 'Asia/Makassar');

        // Validasi ringan untuk input month
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $monthStr = (string) $request->query('month', Carbon::now($tz)->format('Y-m'));

        try {
            [$yy, $mm] = explode('-', $monthStr);
            $start = Carbon::createFromDate((int) $yy, (int) $mm, 1, $tz)->startOfMonth();
        } catch (\Throwable $e) {
            $start = Carbon::now($tz)->startOfMonth();
        }
        $end = (clone $start)->endOfMonth();

        // Pastikan kolom FK di tabel presensis adalah 'karyawan_id' yang merefer ke karyawan.id
        $items = Presensi::where('karyawan_id', $idKaryawan)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('user.riwayat_presensi', [
            'title'         => 'Riwayat Presensi',
            'items'         => $items,
            'current_month' => $start->format('Y-m'),
        ]);
    }
}
