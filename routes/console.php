<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$tz = 'Asia/Makassar';

/**
 * Cek libur nasional via API. Jika API error => anggap BUKAN libur (tetap jalan).
 * Cache per hari.
 */
$isHoliday = function () use ($tz) {
    $today = now($tz)->format('Y-m-d');
    $ttl   = now($tz)->endOfDay()->diffInSeconds(now($tz));

    return Cache::remember("holiday:$today", $ttl, function () use ($tz, $today) {
        try {
            $now  = now($tz);
            $resp = Http::timeout(5)->retry(2, 200)->get(
                'https://api-harilibur.vercel.app/api',
                ['year' => $now->year, 'month' => $now->month]
            );
            if (!$resp->ok()) return false;

            foreach ($resp->json() as $row) {
                if (empty($row['holiday_date'])) continue;
                $date = Carbon::parse($row['holiday_date'])->timezone($tz)->format('Y-m-d');
                if ($date === $today) {
                    return (bool)($row['is_national_holiday'] ?? false);
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::warning('Holiday API error: '.$e->getMessage());
            // REKOMENDASI: fallback tetap jalan => anggap bukan libur
            return false;
        }
    });
};

// Jadwalkan command
Schedule::command('presensi:prepare-today')
    ->timezone($tz)
    ->dailyAt('00:02')
    ->withoutOverlapping()
    ->evenInMaintenanceMode()
    ->when(fn () => ! $isHoliday()); // jalan jika bukan libur

Schedule::command('presensi:finalize-today')
    ->timezone($tz)
    ->dailyAt('23:59')
    ->withoutOverlapping()
    ->evenInMaintenanceMode()
    ->when(fn () => ! $isHoliday());
