<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Cara pakai di route:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,karyawan')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Jika belum login → arahkan ke halaman login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // $roles berisi daftar role yang diizinkan, mis. ['admin'] atau ['admin','karyawan']
        // Trim setiap entri untuk menghindari spasi yang tak sengaja
        $allowed = array_map('trim', $roles);

        // Cek apakah role user saat ini termasuk yang diizinkan (perbandingan ketat/type-strict)
        if (!in_array((string) Auth::user()->role, $allowed, true)) {
            // Tentukan halaman fallback berdasarkan role user saat ini
            // Catatan: jika role tidak 'admin', default diarahkan ke 'user.home'
            $redirect = Auth::user()->role === 'admin' ? 'admin.mode' : 'user.home';

            // Pengaman: hindari loop redirect jika pengguna sudah berada di route tujuan
            if ($request->routeIs($redirect)) {
                // Alternatif: bisa abort(403) jika ingin menolak akses tanpa redirect
                return redirect()->route('login')
                    ->withErrors(['auth' => 'Anda tidak punya akses ke halaman ini.']);
            }

            // Redirect ke halaman fallback + kirim pesan error global
            return redirect()->route($redirect)
                ->withErrors(['auth' => 'Anda tidak punya akses ke halaman ini']);
        }

        // Lolos pengecekan role → lanjut proses request
        return $next($request);
    }
}
