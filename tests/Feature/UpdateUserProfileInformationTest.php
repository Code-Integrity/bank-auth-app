<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('プロフィール更新：通常ユーザーの名前とメールアドレスを更新できる（else側分岐の網羅）', function () {
    $user = User::factory()->create([
        'name' => '古い 名前',
        'email' => 'old@example.com',
    ]);

    $action = new UpdateUserProfileInformation();

    $input = [
        'name' => '新しい 名前',
        'email' => 'new@example.com',
    ];

    $action->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('新しい 名前');
    expect($user->email)->toBe('new@example.com');
});

test('プロフィール更新：メール認証が必要なユーザーがメールアドレスを変更した場合、認証がリセットされ通知が飛ぶ（if側分岐の網羅）', function () {
    Notification::fake();

    // Userモデル自体がMustVerifyEmailを実装している場合はそのままUser::factory()でOKですが、
    // 実装していない場合を想定し、提示いただいた安全な無名クラスのモックを継承します
    $user = new class extends User implements MustVerifyEmail {
        protected $table = 'users';
    };

    $user->fill(User::factory()->raw([
        'name' => '認証 ユーザー',
        'email' => 'verified@example.com',
        'email_verified_at' => now(),
    ]))->save();

    $action = new UpdateUserProfileInformation();

    $input = [
        'name' => '認証 ユーザー',
        'email' => 'changed@example.com',
    ];

    $action->update($user, $input);

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
    expect($user->email)->toBe('changed@example.com');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('プロフィール更新：プロフィール写真をアップロードできる（フォト分岐の完全網羅）', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $action = new UpdateUserProfileInformation();

    // 💡 GD拡張機能に依存せず、ダミーの画像バイナリを直接生成して偽装する
    $dummyJpgContent = "FFD8FFE000104A46494600010101006000600000FFDB004300"; // JPEGのヘッダー模倣テキスト
    $file = UploadedFile::fake()->createWithContent(
        'avatar.jpg',
        $dummyJpgContent
    );

    $input = [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => $file, // isset($input['photo']) を確実に通過
    ];

    $action->update($user, $input);

    $user->refresh();
    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});


test('プロフィール更新：バリデーションに違反する場合はエラーになる（異常系の網羅）', function () {
    $user = User::factory()->create();
    $action = new UpdateUserProfileInformation();

    $invalidInput = [
        'name' => '',
        'email' => 'not-an-email',
    ];

    expect(fn() => $action->update($user, $invalidInput))
        ->toThrow(ValidationException::class);
});

test('ユーザーがメールアドレスを変更した際、再認証・再ベリファイの分岐ルートを完全に通過する（HTTPリクエスト統合テスト）', function () {
    Notification::fake(); // HTTP経由でもメールが飛ぶためfake化

    $user = User::factory()->create([
        'email' => 'original-email@example.com',
        'two_factor_secret' => 'encrypted-secret-string-here',
        'two_factor_recovery_codes' => encrypt(json_encode([])),
    ]);

    $this->actingAs($user);

    $response = $this->put('/user/profile-information', [
        'name'  => $user->name,
        'email' => 'new-secure-email@example.com',
    ]);

    $response->assertRedirect(url('/'));

    $user->refresh();
    expect($user->email)->toBe('new-secure-email@example.com');
});
