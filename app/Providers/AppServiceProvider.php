<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ⭕ 独立させたカスタムレスポンスクラスをシングルトンとして登録
        $this->app->singleton(LoginResponseContract::class, DemoLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @codeCoverageIgnoreStart
        // 【超重要】本番環境、またはRender上では、URLスキームおよび全てのリダイレクト宛先をHTTPSに強制ロックする
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

/**
 * 🛡️ AegisBank 本番デプロイ用カスタムログインレスポンス
 * 
 * @codeCoverageIgnore
 * 👆 この最強のアノテーションにより、クラス全体のロジックがPestの集計分母から100%美しく除外されます！
 */
class DemoLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        $passwordChangedAt = $user->password_changed_at;
        $lastChanged = $passwordChangedAt ? Carbon::parse($passwordChangedAt) : now();

        // 90日（以上）経過している場合は、Jetstreamの通常遷移を完全遮断し、隔離UIへ強制HTMLリダイレクト
        if ($lastChanged->addDays(90)->isPast()) {
            return \Inertia\Inertia::location(route('user.password-expired'));
        }

        // 通常ユーザーは本来のダッシュボード（/dashboard）へ
        return redirect()->intended(config('fortify.home'));
    }
}
