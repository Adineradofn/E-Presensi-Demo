<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use Illuminate\Support\Facades\Storage;

class DataPengajuanIzinController extends Controller
{
    // Endpoint: GET /izin/{izin}/bukti
    // Tujuan: stream file privat (gambar/PDF) langsung ke browser (inline preview)
    // Catatan: {izin} memakai Route Model Binding -> otomatis load model Izin dari parameter URL
    public function showBukti(Izin $izin)
    {
        // Ambil path relatif file bukti dari kolom di database (mis: "private/bukti/abc.pdf")
        $path = $izin->bukti_path;

        // Jika tidak ada path yang tersimpan, balas 404
        abort_unless($path, 404, 'Bukti tidak tersedia.');

        // Pastikan file benar-benar ada pada disk "local"
        // Konvensi: disk 'local' → storage/app (lihat config/filesystems.php)
        // Umumnya file privat diletakkan di storage/app/private agar tidak dipublish oleh web server
        abort_unless(Storage::disk('local')->exists($path), 404, 'File tidak ditemukan.');

        // Dapatkan absolute path di filesystem server (contoh: /var/www/app/storage/app/private/...)
        $abs = Storage::disk('local')->path($path);

        // Deteksi MIME type untuk header Content-Type
        // Gunakan mime_content_type jika tersedia; jika gagal, fallback ke application/octet-stream
        $mime = function_exists('mime_content_type')
            ? (mime_content_type($abs) ?: 'application/octet-stream')
            : 'application/octet-stream';

        // Kembalikan file secara inline:
        // - Content-Disposition: inline → browser akan coba preview (cocok untuk PDF/gambar)
        //   (ganti ke "attachment" jika ingin memaksa download)
        // - Cache-Control diset ketat untuk file privat agar tidak tersimpan di cache publik
        //   (private, no-store, no-cache, must-revalidate)
        // - Filename diambil dari nama file sebenarnya (basename)
        return response()->file($abs, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($abs).'"',
            'Cache-Control'       => 'private, max-age=0, no-store, no-cache, must-revalidate',
        ]);
    }
}
