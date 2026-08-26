<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. 未ログインユーザーはチェックをスキップ
        if (!Auth::check()) {
            return $next($request);
        }

        // 2. パスワード更新画面、ログアウト、またはそれらに関連するリクエストは除外（無限ループ防止）
        // ※ルート名とURLパスの両方で超確実に防衛
        if ($request->routeIs('user.password-expired') || $request->routeIs('logout') || $request->is('user/password-expired*')) {
            return $next($request);
        }

        $user = Auth::user();

        // 3. パスワード変更日時を取得し、確実に「Carbonインスタンス」に変換する
        $passwordChangedAt = $user->password_changed_at;
        $lastChanged = $passwordChangedAt ? Carbon::parse($passwordChangedAt) : now();

        // 4. 90日（以上）経過しているか判定
        if ($lastChanged->addDays(90)->isPast()) {

            // ★実機DBのエラー（500）でアプリ全体が巻き込まれてクラッシュするのを防ぐため、念のためtry-catchで保護
            try {
                // 同一セッションでの過剰なログ埋めを防ぎつつ、セキュリティイベントを監査ログに記録
                AuditLog::create([
                    'user_id' => $user->id,
                    'event' => 'security.password.expired',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload' => json_encode(['last_changed_at' => $lastChanged->toIso8601String()]),
                ]);
            } catch (\Exception $e) {
                // 実機環境のDBにログテーブルが未作成、またはカラム不一致の場合でも、
                // 認証アプリの最優先事項である「隔離」を最優先で続行させるためログエラーは逃がす
                \Log::error('Audit log failed during password expiration: ' . $e->getMessage());
            }

            // 専用のパスワード更新画面へ強制隔離
            return redirect()->route('user.password-expired');
        }

        return $next($request);
    }
}
