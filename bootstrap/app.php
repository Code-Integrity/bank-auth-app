<?php

use App\Http\Middleware\EnsurePasswordNotExpired;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        $middleware->trustProxies(at: '*', headers: 0b11111);

        $middleware->web(append: [
            EnsureTwoFactorEnabled::class,
            EnsurePasswordNotExpired::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
