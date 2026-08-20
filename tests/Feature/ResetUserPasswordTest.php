<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// テスト実行前にSQLite(メモリ)上にテーブルを自動構築する設定を追加
uses(RefreshDatabase::class);

test('パスワードリセット：銀行レベルのポリシーを満たす新しいパスワードでリセットが成功する', function () {
    // 1. テスト用ユーザーの作成（古いパスワードを設定）
    $user = User::factory()->create([
        'password' => Hash::make('Old-Password123!'),
    ]);

    // 2. アクションのインスタンス化
    $action = new ResetUserPassword();

    // 3. ポリシーを満たす新しいパスワード（12文字以上、大文字小文字・数字・記号）を入力
    $input = [
        'password' => 'New-SecurePass2026!',
        'password_confirmation' => 'New-SecurePass2026!',
    ];

    // 4. アクションを実行
    $action->reset($user, $input);

    // 5. データベースのパスワードが新しく更新されていることを検証
    expect(Hash::check('New-SecurePass2026!', $user->fresh()->password))->toBeTrue();
});

test('パスワードリセット：ポリシーを満たさない不正なパスワードはバリデーションで弾かれる', function () {
    // 1. テスト用ユーザーの作成
    $user = User::factory()->create();
    $action = new ResetUserPassword();

    // 2. 不正なパスワードを入力
    $invalidInput = [
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ];

    // 3. バリデーションエラー（ValidationException）が発生することを検証
    expect(fn() => $action->reset($user, $invalidInput))
        ->toThrow(ValidationException::class);
});
