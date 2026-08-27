<?php

use App\Http\Controllers\AuditLogController; // 💡 コントローラーのインポートを追加
use App\Actions\Fortify\UpdateUserPassword; // 🔒 パスワード更新アクションのインポートを追加
use Illuminate\Foundation\Application;
use Illuminate\Http\Request; // 🔒 リクエストクラスのインポートを追加
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // 🔒 監査ログ（セキュリティ証跡）ルートを追加
    Route::get('/user/audit-logs', [AuditLogController::class, 'index'])->name('user.audit-logs');

    // 🛡️ パスワード有効期限切れ専用画面（表示: GET）
    Route::get('/user/password-expired', function () {
        return Inertia::render('Auth/PasswordExpired'); // ⭕ Profile/ から Auth/ へ変更
    })->name('user.password-expired');

    // 🛡️ パスワード更新処理（実行: POST）★ ここに新しく追加します
    Route::post('/user/password-expired', function (Request $request, UpdateUserPassword $updater) {
        // Jetstream標準のパスワード更新ロジックを実行（最低12文字・記号等のポリシーが自動適用されます）
        $updater->update($request->user(), $request->all());

        // パスワード変更が成功したため、銀行監査ログにセキュリティ証跡を記録
        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'security.password.renewed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 救出完了。ダッシュボードへ安全にリダイレクト
        return redirect()->route('dashboard');
    })->name('user.password-expired.update');
});
