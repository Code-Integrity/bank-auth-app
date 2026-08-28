<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @codeCoverageIgnoreStart
        // 【超重要】本番環境環境、またはRender上では、URLスキームおよび全てのリダイレクト宛先をHTTPSに強制ロックする
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
        // @codeCoverageIgnoreEnd

        // 🛡️ レイヤー1: アカウント別のログイン試行制限（1分間に3回）
        RateLimiter::for('login-account', function (Request $request) {
            $email = (string) $request->input('email');
            return Limit::perMinute(3)->by($email)->response(function () {
                return response()->json(['message' => 'Too many login attempts for this account. Locked for 15 minutes.'], 429);
            });
        });

        // 🛡️ レイヤー2: IPアドレス別のログイン試行制限（1分間に5回）
        RateLimiter::for('login-ip', function (Request $request) {
            $ip = (string) $request->ip();
            return Limit::perMinute(5)->by($ip)->response(function () {
                return response()->json(['message' => 'Too many login attempts from this IP. Locked for 15 minutes.'], 429);
            });
        });
    }
}
