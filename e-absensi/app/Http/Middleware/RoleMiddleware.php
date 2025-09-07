<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (session('role') !== $role) {
            // Kalau role tidak sesuai, kembalikan ke login
            return redirect()->route('login')->withErrors(['login' => 'Anda tidak punya akses ke halaman ini']);
        }

        return $next($request);
    }
}
