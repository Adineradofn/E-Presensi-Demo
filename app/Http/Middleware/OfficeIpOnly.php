<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class OfficeIpOnly
{
    public function handle(Request $request, Closure $next)
    {
        $allowed  = config('office.allowed_ips', []);
        $clientIp = $request->headers->get('CF-Connecting-IP') ?? $request->ip();

        if (!IpUtils::checkIp($clientIp, $allowed)) {
            // Tampilkan view tanpa data tambahan
            return response()->view('user.office_only', [], 403);
        }

        return $next($request);
    }
}
