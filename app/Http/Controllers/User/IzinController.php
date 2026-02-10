<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class IzinController extends Controller
{
    public function __construct(
        protected AttendanceService $svc
    ) {}

    // Karyawan yang login (guard default)
    private function currentKaryawan(): ?Karyawan
    {
        /** @var Karyawan|null $user */
        $user = Auth::user();
        return $user instanceof Karyawan ? $user : null;
    }

    // Form pengajuan izin
    public function create()
    {
        $karyawan = $this->currentKaryawan();
        abort_unless($karyawan, 403);

        return view('user.pengajuan_izin_user', [
            'title'    => 'Pengajuan Izin',
            'karyawan' => $karyawan,
        ]);
    }

    // Simpan pengajuan izin
    public function store(Request $r)
    {
        $karyawan = $this->currentKaryawan();
        abort_unless($karyawan, 403);

        $rules = [
            'jenis'            => ['required', Rule::in(['izin','sakit','cuti','izin terlambat','tugas'])],
            'tanggal_mulai'    => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai'  => ['nullable', 'date'],
            'alasan'           => ['required', 'string', 'max:255'],
            // Bukti sekarang WAJIB
            'bukti'            => ['required','file','mimes:jpg,jpeg,png,heic,heif,pdf','max:10240'],
        ];

        $messages = [
            'required'         => ':attribute wajib diisi.',
            'bukti.required'   => 'Bukti wajib diunggah.',
            'date'             => ':attribute harus berupa tanggal yang valid.',
            'after_or_equal'   => ':attribute tidak boleh sebelum hari ini.',
            'string'           => ':attribute harus berupa teks.',
            'max.string'       => ':attribute maksimal :max karakter.',
            'file'             => ':attribute harus berupa file.',
            'mimes'            => ':attribute harus dalam format: jpg, jpeg, png, heic, heif, atau pdf.',
            'max.file'         => 'Ukuran :attribute maksimal 10 MB.',

            'bukti.file'       => 'Bukti harus berupa file yang valid.',
            'bukti.mimes'      => 'Bukti harus berformat JPG, JPEG, PNG, HEIC, HEIF, atau PDF.',
            'bukti.max'        => 'Ukuran bukti maksimal 10 MB.',
        ];

        $attributes = [
            'jenis'            => 'Jenis Izin',
            'tanggal_mulai'    => 'Tanggal Mulai',
            'tanggal_selesai'  => 'Tanggal Selesai',
            'alasan'           => 'Alasan',
            'bukti'            => 'Bukti',
        ];

        $data = $r->validate($rules, $messages, $attributes);

        $tz    = config('app.timezone','Asia/Makassar');
        $now   = Carbon::now($tz);
        $today = $now->toDateString();

        // Normalisasi tanggal_selesai (inklusif), dan khusus 'izin terlambat' wajib 1 hari
        $mulai = Carbon::parse($data['tanggal_mulai'], $tz)->startOfDay();
        $seles = isset($data['tanggal_selesai']) && $data['tanggal_selesai']
            ? Carbon::parse($data['tanggal_selesai'], $tz)->startOfDay()
            : $mulai->copy();

        if ($data['jenis'] === 'izin terlambat') {
            $seles = $mulai->copy(); // paksa satu hari
        }

        // Cek overlap izin (larangan tumpang tindih di range)
        $overlap = Izin::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal_mulai', '<=', $seles->toDateString())
            ->whereDate('tanggal_selesai', '>=', $mulai->toDateString())
            ->exists();

        if ($overlap) {
            return back()
                ->with('error', 'Tanggal yang dipilih sudah memiliki pengajuan izin.')
                ->withInput();
        }

        // Simpan bukti ke disk 'local' (wajib ada)
        $path = null;
        if ($r->hasFile('bukti')) {
            $folder = 'izin_private/'.$now->format('Y-m');
            $ext    = $r->file('bukti')->getClientOriginalExtension();
            $fname  = 'bukti_'.$karyawan->id.'_'.$now->format('Ymd_His').'.'.$ext;
            $path   = $r->file('bukti')->storeAs($folder, $fname, 'local');
        }

        // Tentukan status approval
        $status = in_array($data['jenis'], ['izin','sakit','cuti'], true) ? 'disetujui' : 'pending';

        $izin = Izin::create([
            'karyawan_id'       => $karyawan->id,
            'tanggal_pengajuan' => $today,
            'jenis'             => $data['jenis'],
            'tanggal_mulai'     => $mulai->toDateString(),
            'tanggal_selesai'   => $seles->toDateString(),
            'alasan'            => $data['alasan'],
            'bukti_path'        => $path,
            'status'            => $status,
        ]);

        // Recalculate range secara instan
        $this->svc->recalculateRange($karyawan->id, $mulai, $seles);

        return back()->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    // Streaming bukti (hanya pemilik atau admin)
    public function showBukti(Izin $izin)
    {
        $user    = Auth::user();
        $isOwner = $user && $user->id === $izin->karyawan_id;
        $isAdmin = $user && $user->role === 'admin';
        abort_unless($isOwner || $isAdmin, 403);

        $path = $izin->bukti_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404, 'Bukti tidak ditemukan.');

        // Path absolut ke storage/app/<path>
        $absolutePath = storage_path('app/'.$path);

        $mime = function_exists('mime_content_type')
            ? (mime_content_type($absolutePath) ?: 'application/octet-stream')
            : 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($absolutePath).'"',
            'Cache-Control'       => 'private, max-age=0, no-store, no-cache, must-revalidate',
        ]);
    }

    // Riwayat pengajuan izin (per bulan)
    public function history(Request $request)
    {
        $karyawan = $this->currentKaryawan();
        abort_unless($karyawan, 403);

        $monthParam = $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $monthParam = now()->format('Y-m');
        }
        $year  = (int) substr($monthParam, 0, 4);
        $month = (int) substr($monthParam, 5, 2);

        $items = Izin::where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal_pengajuan', $year)
            ->whereMonth('tanggal_pengajuan', $month)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(['month' => $monthParam]);

        return view('user.riwayat_izin_user', [
            'title'         => 'Riwayat Izin',
            'items'         => $items,
            'karyawan'      => $karyawan,
            'current_month' => $monthParam,
        ]);
    }
}
