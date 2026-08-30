<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Http\Middleware\EnsurePasswordNotExpired;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO | \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB);

        // 💡 【大逆転の安全弁】ログインURL（/login）に対するCSRFセキュリティチェックを強制的に免除します！
        $middleware->validateCsrfTokens(except: [
            'login',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordNotExpired::class,
            \App\Http\Middleware\EnsureTwoFactorEnabled::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
