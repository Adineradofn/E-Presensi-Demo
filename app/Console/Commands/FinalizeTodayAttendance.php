<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Presensi;
use App\Services\AttendanceService;
use Carbon\Carbon;

/**
 * Finalize presensi untuk HARI INI (atau tanggal yang diberikan).
 * - Tidak membuat baris presensi baru (prinsip: ganti, bukan tambah).
 * - Recalc dulu (sinkron izin terbaru), lalu aturan finalize: only-in => invalid.
 */
class FinalizeTodayAttendance extends Command
{
    // ✅ Nama perintah sesuai permintaan
    protected $signature   = 'presensi:finalize-today {date?}';
    protected $description = 'Finalize status presensi hari ini tanpa membuat baris baru.';

    // ⛏️ Constructor DI aman—container bisa instansiasi AttendanceService (param scalar punya default).
    public function __construct(protected AttendanceService $svc)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tz     = config('app.timezone', 'Asia/Makassar');
        $dateIn = $this->argument('date'); // opsional, untuk testing manual
        $date   = $dateIn ? Carbon::parse($dateIn, $tz) : Carbon::now($tz);
        $dateStr = $date->toDateString();

        // Ambil HANYA baris presensi yang SUDAH ada di tanggal tsb
        $items = Presensi::whereDate('tanggal', $dateStr)->get();

        foreach ($items as $p) {
            // 1) Re-sinkron dgn izin terakhir (real-time safety net)
            $this->svc->recalculateDay($p->karyawan_id, $date);

            // 2) Aturan finalize: only-in → invalid (tanpa buat baris baru)
            $p->refresh();
            if ($p->jam_masuk && !$p->jam_pulang && $p->status_presensi !== 'invalid') {
                $p->update(['status_presensi' => 'invalid']);
            }
        }

        $this->info("Finalize presensi selesai untuk {$dateStr}.");
        return Command::SUCCESS;
    }
}
