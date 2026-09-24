<?php

use App\Http\Middleware\DemoBasicAuth;
use App\Http\Middleware\EnsurePasswordNotExpired;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // [Infrastructure Defense] Fully trust Railway reverse proxies to guarantee proper headers.
        $middleware->trustProxies(at: '*', headers: 0b11111);

        // [Outer Shell Defense] Intercept all incoming traffic at the absolute entry point of the web stack.
        // This prevents compute resource exhaustion and bot brute-force attacks before Laravel boot processes.
        $middleware->web(prepend: [
            DemoBasicAuth::class,
        ]);

        // [Financial-Grade Authentication Pipeline] Append core isolation rules at the application layer.
        // These run safely after the session has been securely booted and verified.
        $middleware->web(append: [
            EnsureTwoFactorEnabled::class,
            EnsurePasswordNotExpired::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
