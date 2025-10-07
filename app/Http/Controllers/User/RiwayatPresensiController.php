<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // // untuk ambil user login
use Carbon\Carbon;

class RiwayatPresensiController extends Controller
{
    // // GET /riwayat-absen (user): tampilkan riwayat per bulan (default bulan ini)
    public function index(Request $request)
    {
        // // Ambil id_karyawan dari user login
        $idKaryawan = optional(Auth::user())->id_karyawan;
        abort_unless($idKaryawan, 403); // // pastikan user punya karyawan

        $tz = config('app.timezone', 'Asia/Makassar');

        // // Ambil parameter month (YYYY-MM), default: bulan berjalan
        $monthStr = (string) $request->query('month', Carbon::now($tz)->format('Y-m'));

        // // Hitung range tanggal [start, end] untuk bulan tsb
        try {
            [$yy, $mm] = explode('-', $monthStr);
            $start = Carbon::createFromDate((int) $yy, (int) $mm, 1, $tz)->startOfMonth();
        } catch (\Throwable $e) {
            // // fallback jika input tidak valid
            $start = Carbon::now($tz)->startOfMonth();
        }
        $end = (clone $start)->endOfMonth();

        // // Query riwayat untuk user login
        $items = Presensi::where('id_karyawan', $idKaryawan)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal', 'desc')
            ->get();

        // // Kirim ke view user.riwayat_absen_user
        return view('user.riwayat_absen_user', [
            'title'         => 'Riwayat Presensi',
            'items'         => $items,
            'current_month' => $start->format('Y-m'), // // untuk mengisi <input type="month">
        ]);
    }
}
