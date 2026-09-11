<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->singleton(LoginResponseContract::class, DemoLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @codeCoverageIgnoreStart

        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
        // @codeCoverageIgnoreEnd

        RateLimiter::for('login-account', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(3)->by($email)->response(function () {
                return response()->json(['message' => 'Too many login attempts for this account. Locked for 15 minutes.'], 429);
            });
        });

        RateLimiter::for('login-ip', function (Request $request) {
            $ip = (string) $request->ip();

            return Limit::perMinute(5)->by($ip)->response(function () {
                return response()->json(['message' => 'Too many login attempts from this IP. Locked for 15 minutes.'], 429);
            });
        });
    }
}

/**
 * Custom login response for AegisBank production deployment
 *
 * @codeCoverageIgnore
 */
class DemoLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();
        $passwordChangedAt = $user->password_changed_at;
        $lastChanged = $passwordChangedAt ? Carbon::parse($passwordChangedAt) : now();

        $redirectUrl = $lastChanged->addDays(90)->isPast()
            ? route('user.password-expired')
            : redirect()->intended(config('fortify.home'))->getTargetUrl();

        if ($request->wantsJson() || config('app.env') === 'production') {
            return response()->json([
                'redirect' => $redirectUrl,
            ], 200);
        }

        return redirect()->to($redirectUrl);
    }
}
