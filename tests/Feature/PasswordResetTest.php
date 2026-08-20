<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('reset password link can be requested', function () {
    Notification::fake();

    // 🌟 流出チェックを回避する独自の強力な初期パスワード
    $user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9$wL'),
    ]);

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class);
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('reset password screen can be rendered', function () {
    Notification::fake();

    // 🌟 流出チェックを回避する独自の強力な初期パスワード
    $user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9$wL'),
    ]);

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
        $response = $this->get('/reset-password/' . $notification->token);

        $response->assertStatus(200);

        return true;
    });
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('password can be reset with valid token', function () {
    Notification::fake();

    // 🌟 流出チェックを回避する独自の強力な初期パスワード
    $user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9$wL'),
    ]);

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'K9#mQ2!zP7vX9$wL',              // 🌟 変更点: 流出していない強力な新パスワード
            'password_confirmation' => 'K9#mQ2!zP7vX9$wL', // 🌟 変更点: 流出していない強力な新パスワード（確認用）
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');
