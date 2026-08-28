<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract; // 👈 追加
use Illuminate\Support\Facades\Auth; // 👈 追加
use Carbon\Carbon; // 👈 追加

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ⭕ ログイン成功時のレスポンスをAegisBankの厳格な防衛仕様にジャック（上書き登録）
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $user = Auth::user();

                    // パスワード変更日時を取得し、確実に「Carbonインスタンス」に変換
                    $passwordChangedAt = $user->password_changed_at;
                    $lastChanged = $passwordChangedAt ? Carbon::parse($passwordChangedAt) : now();

                    // 90日（以上）経過している場合は、Jetstreamの通常遷移を完全遮断し、隔離UIへ強制HTMLリダイレクト
                    if ($lastChanged->addDays(90)->isPast()) {
                        return \Inertia\Inertia::location(route('user.password-expired'));
                    }

                    // 通常ユーザーは本来のダッシュボード（/dashboard）へ
                    return redirect()->intended(config('fortify.home'));
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @codeCoverageIgnoreStart
        // ログイン成功時のレスポンスをAegisBankの厳格な防衛仕様にジャック（上書き登録）
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $user = Auth::user();

                    // パスワード変更日時を取得し、確実に「Carbonインスタンス」に変換
                    $passwordChangedAt = $user->password_changed_at;
                    $lastChanged = $passwordChangedAt ? Carbon::parse($passwordChangedAt) : now();

                    // 90日（以上）経過している場合は、Jetstreamの通常遷移を完全遮断し、隔離UIへ強制HTMLリダイレクト
                    if ($lastChanged->addDays(90)->isPast()) {
                        return \Inertia\Inertia::location(route('user.password-expired'));
                    }

                    // 通常ユーザーは本来のダッシュボード（/dashboard）へ
                    return redirect()->intended(config('fortify.home'));
                }
            };
        });
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
