<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// (opsional, biar rapi pakai import)
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\OfficeIpOnly;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 👉 Daftarkan middleware custom di sini
        $middleware->alias([
            'role'       => RoleMiddleware::class,     // sudah ada
            'office.ip'  => OfficeIpOnly::class,       // ⬅️ tambahkan ini
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
