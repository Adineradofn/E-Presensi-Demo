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
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // Normalisasi jadi lowercase & trim
    $allowed = array_map(fn ($r) => strtolower(trim($r)), $roles);
    $current = strtolower((string) Auth::user()->role);

    if (!in_array($current, $allowed, true)) {
        // Tentukan halaman fallback per role
        $redirect = match ($current) {
            'superadmin' => 'superadmin.dashboard',   // atur sesuai route yang Anda buat
            'admin'      => 'admin.mode',             // sudah ada di routes Anda
            default      => 'user.home',              // user
        };

        if ($request->routeIs($redirect)) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'Anda tidak punya akses ke halaman ini.']);
        }

        return redirect()->route($redirect)
            ->withErrors(['auth' => 'Anda tidak punya akses ke halaman ini']);
    }

    return $next($request);
}

}
