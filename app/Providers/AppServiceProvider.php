<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Izin;
use App\Models\Presensi;
use App\Observers\IzinObserver;
use App\Observers\PresensiObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Izin::observe(IzinObserver::class);
        Presensi::observe(PresensiObserver::class);
    }
}
