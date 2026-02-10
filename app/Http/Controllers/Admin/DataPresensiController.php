<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Models\Presensi;

class DataPresensiController extends Controller
{
    /// -------------------------------------------------------------
    /// STREAM FOTO PRIVAT
    /// Endpoint: GET /admin/data-presensi/{presensi}/foto/{jenis}
    /// Tujuan   : Menampilkan foto (masuk/pulang) dari storage privat
    /// Catatan  : {presensi} memakai Route Model Binding
    /// -------------------------------------------------------------
    public function showPhoto(Presensi $presensi, string $jenis)
    {
        /// Tentukan kolom berdasarkan parameter {jenis}: "masuk" atau selain itu dianggap "pulang"
        $col  = $jenis === 'masuk' ? 'foto_masuk' : 'foto_pulang';
        $path = $presensi->{$col};

        /// Jika path tidak ada di database → 404
        abort_unless($path, 404, 'Foto tidak tersedia.');

        /// Pastikan file benar-benar ada di disk "local" (storage/app/...)
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        /// Absolute path di filesystem server untuk dikirim ke response()->file()
        /// PERHATIAN: Jika $path sudah mengandung "private/...", maka
        ///   "storage_path('app/private/' . $path)" berpotensi menjadi "app/private/private/..."
        ///   Saran lebih aman: Storage::disk('local')->path($path)
        $absolute = storage_path('app/private/' . $path);

        /// Deteksi MIME agar browser tahu cara mem-preview (image/jpeg, application/pdf, dll)
        $mime = function_exists('mime_content_type')
            ? (mime_content_type($absolute) ?: 'application/octet-stream')
            : 'application/octet-stream';

        /// Kembalikan file secara inline:
        /// - Content-Disposition: inline → browser akan coba preview
        /// - Cache-Control: set ketat untuk file privat
        return response()->file($absolute, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($absolute) . '"',
            'Cache-Control'       => 'private, max-age=0, no-store, no-cache, must-revalidate',
        ]);
    }
}
