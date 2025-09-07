<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['role:admin'])->group(function () {
    // Dashboard admin
    Route::get('/dashboard', fn () => view('admin.dashboard', ['title' => 'Dashboard']))
        ->name('admin.dashboard');

    // Halaman statis admin lainnya (boleh tetap pakai closure)
    Route::get('/mode', fn () => view('mode.mode', ['title' => 'Mode']))
        ->name('admin.mode');

    Route::get('/kehadiran', fn () => view('admin.kehadiran', ['title' => 'Kehadiran']))
        ->name('admin.kehadiran');

    Route::get('/pengajuan-izin', fn () => view('admin.pengajuan-izin', ['title' => 'Pengajuan Izin']))
        ->name('admin.pengajuan.izin');

    // ===== Data Karyawan (Controller) =====
    // List + search
    Route::get('/data-karyawan', [KaryawanController::class, 'index'])
        ->name('admin.data.karyawan');

    // Create (AJAX fetch POST)
    Route::post('/data-karyawan', [KaryawanController::class, 'store'])
        ->name('admin.data.karyawan.store');

    // Update data (AJAX fetch PUT via _method)
    Route::put('/data-karyawan/{id}', [KaryawanController::class, 'update'])
        ->name('admin.data.karyawan.update');

    // Update password (AJAX fetch PUT via _method)
    Route::put('/data-karyawan/{id}/password', [KaryawanController::class, 'updatePassword'])
        ->name('admin.data.karyawan.password.update');

    // Delete (AJAX fetch DELETE via _method)
    Route::delete('/data-karyawan/{id}', [KaryawanController::class, 'destroy'])
        ->name('admin.data.karyawan.destroy');
});

/*
|--------------------------------------------------------------------------
| KARYAWAN ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['role:karyawan'])->group(function () {
    Route::get('/home', fn () => view('user.home_user', ['title' => 'Home']))
        ->name('user.home');

    Route::get('/absensi', fn () => view('user.absensi_user', ['title' => 'Absensi']))
        ->name('user.absensi');

    Route::get('/Izin', fn () => view('user.pengajuan_izin_user', ['title' => 'Pengajuan Izin)']))
        ->name('user.pengajuan_izin');

    Route::get('/riwayat-absen', fn () => view('user.riwayat_absen_user', ['title' => 'Riwayat Absen']))
        ->name('user.riwayat_absen');

    Route::get('/riwayat-izin', fn () => view('user.riwayat_izin_user', ['title' => 'Riwayat Izin']))
        ->name('user.riwayat_izin');
        

});
