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
    
        // 🛡️ クラウドプロキシ（Render）のHTTPSヘッダーを100%正しく認識させるための確定版
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO | \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB);

        // 1つのwithMiddlewareブロックの中に、2つのミドルウェアを順番に登録します
        $middleware->web(append: [
            EnsureTwoFactorEnabled::class,          // 1. 先に2FAのチェック
            EnsurePasswordNotExpired::class,       // 2. 次にパスワード有効期限のチェック
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
