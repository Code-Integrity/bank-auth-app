<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->two_factor_secret) {
            // 🌟 モックを破壊する routeIs や named を一切使わず、100%安全なパスベース判定（is）に統一
            $isExempt = $request->is([
                'user/profile',                  // profile.show のパス
                'logout',                        // logout のパス
                'user/two-factor-authentication', // two-factor.* のベースパス
                'user/two-factor-qr-code',
                'user/two-factor-secret-key',
                'user/two-factor-recovery-codes',
                'user/confirm-password',         // password.confirm のパス
                'user/confirmed-password-status'
            ]);

            if ($isExempt) {
                return $next($request);
            }

            // 監査ログを記録してプロフィール画面へリダイレクト隔離
            if (class_exists('\App\Models\AuditLog')) {
                \App\Models\AuditLog::log('middleware.2fa.redirect', $user->id, [
                    'attempted_url' => $request->fullUrl()
                ]);
            }

            return redirect()->route('profile.show');
        }

        return $next($request);
    }
}
