<?php

use App\Models\User;
use App\Http\Middleware\EnsureTwoFactorEnabled; // 🌟 ミドルウェアのインポート
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    // 🌟 隔離ミドルウェアを一時的にバイパス
    $this->withoutMiddleware(EnsureTwoFactorEnabled::class);

    // 🌟 初期パスワードを厳格なポリシー（12文字以上、大文字小文字・数字・記号）に適合させて生成
    $this->actingAs($user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9$wL'),
    ]));

    // 🌟 新しいパスワードも、ポリシーを満たし、かつ過去に流出していない強力なランダム値を指定
    $this->put('/user/password', [
        'current_password' => 'K9#mQ2!zP7vX9$wL',
        'password' => 'A3$rX8*pN2tM5#qV',
        'password_confirmation' => 'A3$rX8*pN2tM5#qV',
    ]);

    // 🌟 データベースのパスワードが「A3$rX8...」に正しく更新されたかを検証
    expect(Hash::check('A3$rX8*pN2tM5#qV', $user->fresh()->password))->toBeTrue();
});

test('current password must be correct', function () {
    // 🌟 隔離ミドルウェアを一時的にバイパス
    $this->withoutMiddleware(EnsureTwoFactorEnabled::class);

    $this->actingAs($user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9$wL'),
    ]));

    // 🌟 現在のパスワードをわざと「wrong-password」にしてリクエスト
    $response = $this->put('/user/password', [
        'current_password' => 'wrong-password',
        'password' => 'A3$rX8*pN2tM5#qV',
        'password_confirmation' => 'A3$rX8*pN2tM5#qV',
    ]);

    // 🌟 現在のパスワードが違うため、エラーが発生することを確認
    $response->assertSessionHasErrors();

    // 🌟 パスワードは更新されず、元の「K9#mQ2...」のままであることを検証
    expect(Hash::check('K9#mQ2!zP7vX9$wL', $user->fresh()->password))->toBeTrue();
});

test('new passwords must match', function () {
    // 🌟 隔離ミドルウェアを一時的にバイパス
    $this->withoutMiddleware(EnsureTwoFactorEnabled::class);

    $this->actingAs($user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9$wL'),
    ]));

    // 🌟 確認用パスワードをわざと「wrong-password」（不一致）にしてリクエスト
    $response = $this->put('/user/password', [
        'current_password' => 'K9#mQ2!zP7vX9$wL',
        'password' => 'A3$rX8*pN2tM5#qV',
        'password_confirmation' => 'wrong-password',
    ]);

    // 🌟 パスワード不一致のため、エラーが発生することを確認
    $response->assertSessionHasErrors();

    // 🌟 パスワードは更新されず、元の「K9#mQ2...」のままであることを検証
    expect(Hash::check('K9#mQ2!zP7vX9$wL', $user->fresh()->password))->toBeTrue();
});
