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

    /// -------------------------------------------------------------
    /// EXPORT CSV
    /// Endpoint: GET /admin/data-presensi/export
    /// Query   :
    ///   - mode=hari|bulan|tahun (default: hari)
    ///   - date=YYYY-MM-DD (untuk mode=hari)
    ///   - month=YYYY-MM (untuk mode=bulan)
    ///   - year=YYYY (untuk mode=tahun)
    ///   - q=kata kunci (filter nama/divisi/jabatan/status/tanggal)
    /// Output   : File CSV (dengan BOM UTF-8 agar ramah Excel)
    /// -------------------------------------------------------------
    public function export(Request $request): StreamedResponse
    {
        /// Ambil mode agregasi dan timezone aplikasi
        $mode  = $request->query('mode', 'hari'); // hari|bulan|tahun
        $tz    = config('app.timezone', 'Asia/Makassar');
        $today = Carbon::now($tz)->toDateString();

        /// Tentukan rentang tanggal ($start, $end) serta label nama file ($label)
        if ($mode === 'bulan') {
            /// Jika ?month=YYYY-MM tidak diberikan → pakai bulan berjalan
            $month = $request->query('month');
            $start = $month ? Carbon::parse($month . '-01', $tz)->startOfMonth() : Carbon::now($tz)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
            $label = 'bulan_' . $start->format('Y-m');
        } elseif ($mode === 'tahun') {
            /// Jika ?year=YYYY tidak diberikan → pakai tahun berjalan
            $year  = $request->query('year');
            $start = $year ? Carbon::createFromDate($year, 1, 1, $tz)->startOfYear() : Carbon::now($tz)->startOfYear();
            $end   = (clone $start)->endOfYear();
            $label = 'tahun_' . $start->format('Y');
        } else {
            /// Mode harian: pakai ?date=YYYY-MM-DD, default hari ini (sesuai timezone app)
            $date  = $request->query('date', $today);
            $start = Carbon::parse($date, $tz)->startOfDay();
            $end   = Carbon::parse($date, $tz)->endOfDay();
            $label = 'hari_' . $start->toDateString();
        }

        /// Query dasar: join relasi karyawan + filter berdasarkan rentang tanggal
        $query = Presensi::with('karyawan')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);

        /// Pencarian bebas (?q=...) pada nama/divisi/jabatan/status/tanggal
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereHas('karyawan', function ($k) use ($q) {
                    $k->where('nama', 'like', "%{$q}%")
                      ->orWhere('divisi', 'like', "%{$q}%")
                      ->orWhere('jabatan', 'like', "%{$q}%");
                })
                ->orWhere('status',  'like', "%{$q}%")
                ->orWhere('tanggal', 'like', "%{$q}%");
            });
        }

        /// Urutan: terbaru dulu per tanggal, lalu per id_karyawan
        $items = $query->orderBy('tanggal', 'desc')->orderBy('id_karyawan')->get();

        /// Nama file hasil unduhan
        $filename = 'presensi_' . $label . '.csv';

        /// Stream CSV agar hemat memori untuk dataset besar
        return response()->streamDownload(function () use ($items) {
            /// Buka output stream
            $out = fopen('php://output', 'w');

            /// Tulis BOM UTF-8 supaya Excel Windows menampilkan karakter dengan benar
            echo "\xEF\xBB\xBF";

            /// Header kolom
            fputcsv($out, [
                'ID Presensi',
                'Nama',
                'Divisi',
                'Jabatan',
                'Tanggal',
                'Jam Masuk',
                'Jam Pulang',
                'Status',
                'IP Address',
            ]);

            /// Isi data baris per baris
            foreach ($items as $row) {
                /// Ambil data relasi karyawan (gunakan default '-' jika null)
                $nama   = $row->karyawan->nama    ?? '-';
                $divisi = $row->karyawan->divisi  ?? '-';
                $jab    = $row->karyawan->jabatan ?? '-';

                /// TULISAN PENTING:
                /// - optional($row->tanggal)->format('Y-m-d') mengasumsikan 'tanggal' dicast ke Carbon/Date.
                ///   Pastikan cast di Model: protected $casts = ['tanggal' => 'date', 'jam_masuk' => 'datetime', 'jam_pulang' => 'datetime'];
                fputcsv($out, [
                    $row->id_presensi,
                    $nama,
                    $divisi,
                    $jab,
                    optional($row->tanggal)->format('Y-m-d'),
                    optional($row->jam_masuk)->format('H:i:s'),
                    optional($row->jam_pulang)->format('H:i:s'),
                    $row->status,
                    $row->ip_address,
                ]);
            }

            /// Tutup stream
            fclose($out);
        }, $filename, [
            /// Header download CSV
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
