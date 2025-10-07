<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FotoKaryawanController;
use App\Http\Controllers\Admin\DataPresensiController;
use App\Http\Controllers\Admin\DataPengajuanIzinController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\User\RiwayatPresensiController;
use App\Http\Controllers\User\IzinController;
use App\Http\Controllers\User\PresensiController;
use App\Livewire\Admin\DataKaryawan;
use App\Livewire\Admin\DataPresensi;
use App\Livewire\Admin\DataPengajuanIzin;
use App\Livewire\Admin\RekapPresensi;
use App\Http\Controllers\Admin\RekapPresensiExportController;


Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn() => to_route('admin.mode'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

        Route::get('/mode', fn() => view('admin.mode.mode', ['title' => 'Mode']))->name('mode');

        Route::get('/data-karyawan', DataKaryawan::class)->name('data.karyawan');

        Route::get('/data-karyawan/{karyawan}/foto', [FotoKaryawanController::class, 'photo'])
            ->whereNumber('karyawan')
            ->name('data.karyawan.foto');

        // Presensi (Livewire) - tanpa edit status
        Route::get('/data-presensi', DataPresensi::class)->name('data.presensi');

        // Foto presensi (binding pk 'id')
        Route::get('/data-presensi/{presensi}/foto/{jenis}', [DataPresensiController::class, 'showPhoto'])
            ->where(['jenis' => 'masuk|pulang'])
            ->name('data.presensi.foto.show');

        // (Jika Anda masih memakai export)
        Route::get('/data-presensi/export', [DataPresensiController::class, 'export'])->name('data.presensi.export');

        // Pengajuan Izin
        Route::get('/pengajuan-izin', DataPengajuanIzin::class)->name('pengajuan-izin');
        Route::get('/izin/{izin}/bukti', [DataPengajuanIzinController::class, 'showBukti'])->name('izin.bukti.show');

        // REKAP PRESENSI (BARU)
        // routes/web.php (di grup admin)
        Route::get('/rekap-presensi', \App\Livewire\Admin\RekapPresensi::class)->name('data.rekap-presensi');
        Route::get('/rekap-presensi/export', RekapPresensiExportController::class)->name('rekap-presensi.export');
    });

Route::middleware(['auth', 'role:admin,karyawan'])->group(function () {
    Route::get('/home', fn() => view('user.home_user', ['title' => 'Home']))->name('user.home');

    Route::get('/data-karyawan/{karyawan}/foto', [FotoKaryawanController::class, 'photo'])
        ->whereNumber('karyawan')
        ->name('data.karyawan.foto');

    // Presensi (view)
    Route::middleware('office.ip')->group(function () {
        Route::get('/presensi', fn() => view('user.presensi_user', ['title' => 'Presensi']))->name('user.presensi');
        Route::post('/presensi/check-in', [PresensiController::class, 'checkIn'])->name('presensi.checkin');
        Route::post('/presensi/check-out', [PresensiController::class, 'checkOut'])->name('presensi.checkout');
        Route::get('/presensi/status-today', [PresensiController::class, 'statusToday'])->name('presensi.status.today');
    });

    // Izin
    Route::get('/izin', [IzinController::class, 'create'])->name('user.pengajuan_izin');
    Route::post('/izin', [IzinController::class, 'store'])->name('user.izin.store');
    Route::get('/izin/bukti/{izin}', [IzinController::class, 'showBukti'])->name('user.izin.bukti.show');

    // Riwayat
    Route::get('/riwayat-absen', [RiwayatPresensiController::class, 'index'])->name('user.riwayat_absen');
    Route::get('/riwayat-izin', [IzinController::class, 'history'])->name('user.riwayat_izin');
});
