<?php

use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $this->withoutMiddleware(EnsureTwoFactorEnabled::class);

    $this->actingAs($user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9'),
    ]));

    $this->put('/user/password', [
        'current_password' => 'K9#mQ2!zP7vX9',
        'password' => 'A3*pN2tM5#qV',
        'password_confirmation' => 'A3*pN2tM5#qV',
    ]);

    expect(Hash::check('A3*pN2tM5#qV', $user->fresh()->password))->toBeTrue();
});

test('current password must be correct', function () {
    $this->withoutMiddleware(EnsureTwoFactorEnabled::class);

    $this->actingAs($user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9'),
    ]));

    $response = $this->put('/user/password', [
        'current_password' => 'wrong-password',
        'password' => 'A3*pN2tM5#qV',
        'password_confirmation' => 'A3*pN2tM5#qV',
    ]);

    $response->assertSessionHasErrors();

    expect(Hash::check('K9#mQ2!zP7vX9', $user->fresh()->password))->toBeTrue();
});

test('new passwords must match', function () {
    $this->withoutMiddleware(EnsureTwoFactorEnabled::class);

    $this->actingAs($user = User::factory()->create([
        'password' => Hash::make('K9#mQ2!zP7vX9'),
    ]));

    $response = $this->put('/user/password', [
        'current_password' => 'K9#mQ2!zP7vX9',
        'password' => 'A3*pN2tM5#qV',
        'password_confirmation' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();

    expect(Hash::check('K9#mQ2!zP7vX9', $user->fresh()->password))->toBeTrue();
});
