<?php

use App\Http\Middleware\DemoBasicAuth;
use App\Http\Middleware\EnsurePasswordNotExpired;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // bootstrap/app.php

    ->withMiddleware(function (Middleware $middleware) {
        // [Infrastructure Defense] Trust ALL proxies and headers to accurately detect HTTPS under Railway.
        // This instantly fixes the 419 Page Expired (CSRF) issues caused by reverse proxy SSL termination.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_AWS_ELB | Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

        // [Outer Shell Defense] Intercept all incoming traffic at the absolute entry point of the web stack.
        $middleware->web(prepend: [
            DemoBasicAuth::class,
        ]);

        // [Financial-Grade Authentication Pipeline] Append core isolation rules at the application layer.
        $middleware->web(append: [
            EnsureTwoFactorEnabled::class,
            EnsurePasswordNotExpired::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {})->create();
