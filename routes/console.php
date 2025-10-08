<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\HariLibur; // ⬅️ tambahkan import model libur manual

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pakai timezone dari config (fallback Asia/Makassar)
$tz = config('app.timezone', 'Asia/Makassar');

/**
 * Determinasi "libur hari ini?"
 * - PRIORITAS 1: Libur manual (tabel hari_libur) dengan rentang [tanggal_mulai..tanggal_selesai] (inklusif).
 *                Jika ada yang match → TRUE (libur).
 * - PRIORITAS 2: Libur nasional dari API eksternal.
 *                Jika API error → fallback FALSE (anggap bukan libur).
 * - Hasil dicache sampai akhir hari.
 */
$isHoliday = function () use ($tz) {
    $today = now($tz)->format('Y-m-d');
    $ttl   = now($tz)->endOfDay()->diffInSeconds(now($tz));

    return Cache::remember("holiday:$today", $ttl, function () use ($tz, $today) {
        // 1) Cek libur manual (override)
        $hasManualHoliday = HariLibur::query()
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->exists();

        if ($hasManualHoliday) {
            return true; // ada libur manual → libur
        }

        // 2) Cek libur nasional via API (fallback)
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

                // Normalisasi tanggal API → Y-m-d (zona lokal)
                $date = Carbon::parse($row['holiday_date'])->timezone($tz)->format('Y-m-d');

                if ($date === $today) {
                    // Hanya anggap libur jika bertipe libur nasional
                    return (bool)($row['is_national_holiday'] ?? false);
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning('Holiday API error: ' . $e->getMessage());
            // Fallback: anggap bukan libur jika API bermasalah
            return false;
        }
    });
};

// ====================== Scheduler ======================

Schedule::command('presensi:prepare-today')
    ->timezone($tz)
    ->dailyAt('00:02')
    ->withoutOverlapping()
    ->evenInMaintenanceMode()
    ->when(fn () => ! $isHoliday()); // jalan hanya jika BUKAN libur (manual/ API)

Schedule::command('presensi:finalize-today')
    ->timezone($tz)
    ->dailyAt('23:59')
    ->withoutOverlapping()
    ->evenInMaintenanceMode()
    ->when(fn () => ! $isHoliday()); // jalan hanya jika BUKAN libur (manual/ API)
