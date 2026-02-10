<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\HariLibur;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pakai timezone dari config, fallback ke Asia/Makassar
$tz = config('app.timezone', 'Asia/Makassar');

/**
 * Cek libur (true = libur) dari DB + fallback API, dengan cache harian.
 * - DB MENANG: bila ada record yang overlap hari ini, langsung true.
 * - Jika API error/invalid => anggap BUKAN libur (false) agar scheduler tetap jalan.
 */
$isHoliday = function () use ($tz): bool {
    $now    = now($tz);
    $today  = $now->format('Y-m-d');
    $ttl    = $now->endOfDay()->diffInSeconds($now);

    return Cache::remember("holiday:$today", $ttl, function () use ($tz, $today) {
        // 1) CEK DB (overlap range)
        $exists = HariLibur::query()
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->exists();

        if ($exists) {
            return true; // DB veto
        }

        // 2) FALLBACK API
        try {
            $now  = now($tz);
            $resp = Http::timeout(5)
                ->retry(2, 200)
                ->get('https://api-harilibur.vercel.app/api', [
                    'year'  => $now->year,
                    'month' => $now->month,
                ]);

            if (!$resp->ok()) return false;

            $rows = $resp->json();
            if (!is_array($rows)) return false;

            foreach ($rows as $row) {
                if (empty($row['holiday_date'])) continue;

                $date = Carbon::parse($row['holiday_date'])
                    ->timezone($tz)
                    ->format('Y-m-d');

                if ($date === $today) {
                    return (bool)($row['is_national_holiday'] ?? false);
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::warning('Holiday API error: '.$e->getMessage());
            return false; // fallback: tetap jalan
        }
    });
};

// ====================== Scheduler ======================
// Pakai CRON DOW (Senin–Sabtu) agar Minggu tidak pernah jalan.
// Prepare: 00:02, Finalize: 23:59. Libur nasional mem-veto via ->when().

Schedule::command('presensi:prepare-today')
    ->timezone($tz)
    ->cron('2 0 * * 1-6')     // 00:02, Senin–Sabtu
    ->onOneServer()
    ->withoutOverlapping()
    ->evenInMaintenanceMode()
    ->when(fn () => ! $isHoliday());

Schedule::command('presensi:finalize-today')
    ->timezone($tz)
    ->cron('59 23 * * 1-6')   // 23:59, Senin–Sabtu
    ->onOneServer()
    ->withoutOverlapping()
    ->evenInMaintenanceMode()
    ->when(fn () => ! $isHoliday());
