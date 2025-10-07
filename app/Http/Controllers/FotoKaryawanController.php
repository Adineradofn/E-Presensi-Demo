<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FotoKaryawanController extends Controller
{
    // Streaming foto privat (inline).
    // HANYA pemilik yang boleh akses (admin juga tidak boleh lihat milik orang lain).
    public function photo(Karyawan $karyawan)
    {
        // Ambil user yang sedang login
        $user = Auth::user();
        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED); // 401 jika belum login
        }

        // Tentukan ID karyawan yang dimiliki user login
        // Catatan: gunakan id_karyawan jika ada, fallback ke id (jaga-jaga skema lama)
        $ownId = $user->id_karyawan ?? $user->id;

        // Bandingkan dengan ID dari parameter (Route Model Binding Karyawan)
        // Jika bukan miliknya, tolak akses (meski role admin sekalipun)
        if ((int) $ownId !== (int) $karyawan->getKey()) {
            abort(Response::HTTP_FORBIDDEN); // 403 untuk akses terlarang
        }

        // Pastikan path foto ada dan file eksis di storage privat (disk 'local')
        if (!$karyawan->foto || !Storage::disk('local')->exists($karyawan->foto)) {
            abort(Response::HTTP_NOT_FOUND); // 404 jika tidak ditemukan
        }

        // Ambil absolute path untuk dikirim ke response()->file()
        // Disk 'local' biasanya mengarah ke storage/app (atau storage/app/private sesuai konfigurasi)
        $path = Storage::disk('local')->path($karyawan->foto);

        // Kembalikan file secara inline agar dapat di-preview oleh browser
        // Catatan: mime_content_type dapat gagal di environment tertentu; pertimbangkan fallback default.
        return response()->file($path, [
            'Content-Type'        => mime_content_type($path),
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control'       => 'private, no-store, max-age=0', // hindari cache publik
        ]);
    }
}
