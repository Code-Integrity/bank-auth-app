<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    /**
     * ユーザー自身の監査ログ一覧画面を表示
     */
    public function index(Request $request)
    {
        // ログイン中ユーザーの監査ログを最新順でページネーション取得
        $logs = $request->user()->auditLogs()
            ->latest()
            ->paginate(10)
            ->through(fn($log) => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $this->formatEventDescription($log->event),
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->isoFormat('YYYY-MM-DD HH:mm:ss'),
            ]);

        return Inertia::render('Profile/AuditLogs', [
            'logs' => $logs
        ]);
    }

    /**
     * ログイベント種別をユーザー向けの分かりやすい文言に変換
     */
    private function formatEventDescription(string $event): string
    {
        return match ($event) {
            'auth.login.success' => 'ログイン成功',
            'auth.login.failed' => 'ログイン失敗（不正アクセスの可能性）',
            'middleware.2fa.redirect' => '2要素認証未設定による強制隔離（保護イベント）',
            default => '未定義のセキュリティイベント',
        };
    }
}
