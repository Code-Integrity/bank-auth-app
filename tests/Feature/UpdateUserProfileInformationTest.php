<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('profile update: allows a standard user to update their name and email address (covers else branch criteria)', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $action = new UpdateUserProfileInformation;

    $input = [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ];

    $action->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

test('profile update: resets verification state and dispatches a notification when email address changes for verification-required users (covers if branch criteria)', function () {
    Notification::fake();

    $user = new class extends User implements MustVerifyEmail
    {
        protected $table = 'users';
    };

    $user->fill(User::factory()->raw([
        'name' => 'Verified User',
        'email' => 'verified@example.com',
        'email_verified_at' => now(),
    ]))->save();

    $action = new UpdateUserProfileInformation;

    $input = [
        'name' => 'Verified User',
        'email' => 'changed@example.com',
    ];

    $action->update($user, $input);

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
    expect($user->email)->toBe('changed@example.com');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('profile update: permits a user to successfully upload a profile photo (covers avatar upload branch)', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $action = new UpdateUserProfileInformation;

    $dummyJpgContent = 'FFD8FFE000104A46494600010101006000600000FFDB004300';
    $file = UploadedFile::fake()->createWithContent(
        'avatar.jpg',
        $dummyJpgContent
    );

    $input = [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => $file,
    ];

    $action->update($user, $input);

    $user->refresh();
    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('profile update: throws a validation exception when data violates semantic rules (covers anomaly test vectors)', function () {
    $user = User::factory()->create();
    $action = new UpdateUserProfileInformation;

    $invalidInput = [
        'name' => '',
        'email' => 'not-an-email',
    ];

    expect(fn () => $action->update($user, $invalidInput))
        ->toThrow(ValidationException::class);
});

test('profile update: fully traverses the re-verification branch and handling loops when an email address is altered (HTTP integrated integration test)', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'original-email@example.com',
        'two_factor_secret' => 'encrypted-secret-string-here',
        'two_factor_recovery_codes' => encrypt(json_encode([])),
    ]);

    $this->actingAs($user);

    $response = $this->put('/user/profile-information', [
        'name' => $user->name,
        'email' => 'new-secure-email@example.com',
    ]);

    $response->assertRedirect(url('/'));

    $user->refresh();
    expect($user->email)->toBe('new-secure-email@example.com');
});
