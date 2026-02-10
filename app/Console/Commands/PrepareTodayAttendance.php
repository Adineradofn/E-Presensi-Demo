<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Karyawan;
use App\Models\JamKerja;
use App\Models\Jadwal;
use App\Models\Presensi;

/**
 * Menyiapkan jadwal & presensi default (alpa) untuk HARI INI (hari kerja).
 * Jalankan 00:02 setiap hari kerja.
 */
class PrepareTodayAttendance extends Command
{
    protected $signature = 'presensi:prepare-today';
    protected $description = 'Generate jadwal & presensi default (status "alpa") untuk semua karyawan hari ini (hari kerja).';

    public function handle(): int
    {
        $tz    = config('app.timezone', 'Asia/Makassar');
        $now   = Carbon::now($tz);
        $today = $now->toDateString();
        $dow   = $now->dayOfWeekIso; // 1=Senin ... 6=Sabtu, 7=Minggu

        // HANYA Minggu yang libur (Sabtu adalah hari kerja)
        if ($dow === 7) {
            $this->info("Minggu {$today} — tidak menyiapkan presensi.");
            return self::SUCCESS;
        }

        // Pilih jam kerja berdasarkan hari:
        // - Sabtu (6)  -> 'sabtu'
        // - Lainnya     -> 'senin_jumat'
        $jkName = ($dow === 6) ? 'sabtu' : 'senin-jumat';

        // Ambil JamKerja hasil seed; JANGAN buat baru di sini
        $jk = JamKerja::where('nama', $jkName)->first();
        if (!$jk) {
            $this->error("JamKerja '{$jkName}' tidak ditemukan. Pastikan sudah di-seed sesuai nama tersebut.");
            return self::FAILURE;
        }

        // Siapkan jadwal & presensi default (status 'alpa') untuk semua karyawan
        $karyawans = Karyawan::all();

        foreach ($karyawans as $kr) {
            // Jadwal untuk hari ini (idempoten)
            $jadwal = Jadwal::firstOrCreate(
                [
                    'karyawan_id' => $kr->id,
                    'tanggal'     => $today,
                ],
                [
                    'jam_kerja_id' => $jk->id,
                    'libur'        => false,
                ]
            );

            // Presensi default untuk hari ini (idempoten)
            Presensi::firstOrCreate(
                [
                    'karyawan_id' => $kr->id,
                    'tanggal'     => $today,
                ],
                [
                    'jadwal_id'       => $jadwal->id,
                    'status_presensi' => 'alpa',
                ]
            );
        }

        $this->info("Disiapkan presensi & jadwal untuk {$today} (jam kerja: {$jkName}).");
        return self::SUCCESS;
    }
}
