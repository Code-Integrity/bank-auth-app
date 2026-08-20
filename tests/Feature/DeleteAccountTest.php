<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('user accounts can be deleted', function () {
    // 🌟 変更点: 2FA設定済みのユーザーを生成して隔離ミドルウェアをバイパス
    $this->actingAs($user = User::factory()->create([
        'two_factor_secret' => encrypt('secret-key'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]));

    $this->delete('/user', [
        'password' => 'password', // ※銀行レベルポリシー（最低12文字など）に合わせて必要なら 'Password1234!' 等に変更
    ]);

    expect($user->fresh())->toBeNull();
})->skip(function () {
    return ! Features::hasAccountDeletionFeatures();
}, 'Account deletion is not enabled.');

test('correct password must be provided before account can be deleted', function () {
    // 🌟 変更点: 2FA設定済みのユーザーを生成して隔離ミドルウェアをバイパス
    $this->actingAs($user = User::factory()->create([
        'two_factor_secret' => encrypt('secret-key'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]));

    $this->delete('/user', [
        'password' => 'wrong-password',
    ]);

    expect($user->fresh())->not->toBeNull();
})->skip(function () {
    return ! Features::hasAccountDeletionFeatures();
}, 'Account deletion is not enabled.');
