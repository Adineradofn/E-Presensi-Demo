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
        $dow   = $now->dayOfWeekIso; // 6=Sabtu,7=Minggu

        // Weekend => skip
        if (in_array($dow, [6,7], true)) {
            $this->info("Weekend {$today} — tidak menyiapkan presensi.");
            return self::SUCCESS;
        }

        // Ambil JamKerja default
        $jk = JamKerja::firstOrCreate(
            ['nama' => 'Senin-Sabtu'],
            [
                'jam_masuk' => '08:00:00',
                'jam_pulang'=> '16:00:00',
                'masuk_buka_sebelum' => 0,
                'masuk_tutup_sesudah'=> 0,
                'pulang_buka_sebelum'=> 0,
                'pulang_tutup_sesudah'=> 0,
            ]
        );

        $karyawans = Karyawan::all();

        foreach ($karyawans as $kr) {
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

        $this->info("Disiapkan presensi & jadwal untuk {$today}.");
        return self::SUCCESS;
    }
}
