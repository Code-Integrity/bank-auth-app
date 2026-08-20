<?php

use App\Models\User;

test('profile information can be updated', function () {
    // 🌟 2FA設定済みのユーザーを生成して隔離ミドルウェアをバイパス
    $this->actingAs($user = User::factory()->create([
        'two_factor_secret' => encrypt('secret-key'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]));

    // 🌟 プロフィール情報の更新リクエストを送信
    $this->put('/user/profile-information', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
    ]);

    // 🌟 データベースの値が正しく更新されたかを検証
    expect($user->fresh())
        ->name->toEqual('Test Name')
        ->email->toEqual('test@example.com');
});
