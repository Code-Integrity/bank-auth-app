<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->two_factor_secret) {

            $isExempt = $request->is([
                'user/profile',
                'logout',
                'user/two-factor-authentication',
                'user/two-factor-qr-code',
                'user/two-factor-secret-key',
                'user/two-factor-recovery-codes',
                'user/confirm-password',
                'user/confirmed-password-status',
            ]);

            if ($isExempt) {
                return $next($request);
            }

            if (class_exists('\App\Models\AuditLog')) {
                AuditLog::log('middleware.2fa.redirect', $user->id, [
                    'attempted_url' => $request->fullUrl(),
                ]);
            }

            return redirect()->route('profile.show');
        }

        return $next($request);
    }
}
