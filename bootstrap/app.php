<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Http\Middleware\EnsurePasswordNotExpired; // インポートをスッキリ整頓

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Renderのロードバランサー（プロキシ）を信頼する設定を追加
        $middleware->trustProxies(at: '*');
        // ★ 1つのwithMiddlewareブロックの中に、2つのミドルウェアを順番に登録します
        $middleware->web(append: [
            EnsureTwoFactorEnabled::class,          // 1. 先に2FAのチェック
            EnsurePasswordNotExpired::class,       // 2. 次にパスワード有効期限のチェック
        ]);
    }) // ← ここで綺麗に閉じます


    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
