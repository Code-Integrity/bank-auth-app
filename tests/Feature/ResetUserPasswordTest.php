<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('password reset: successfully resets password when the new input satisfies banking-grade complexity rules', function () {

    $user = User::factory()->create([
        'password' => Hash::make('Old-Password123!'),
    ]);

    $action = new ResetUserPassword;

    $input = [
        'password' => 'New-SecurePass2026!',
        'password_confirmation' => 'New-SecurePass2026!',
    ];

    $action->reset($user, $input);

    expect(Hash::check('New-SecurePass2026!', $user->fresh()->password))->toBeTrue();
});

test('password reset: throws a validation exception and rejects the operation when the password policy is violated', function () {

    $user = User::factory()->create();
    $action = new ResetUserPassword;

    $invalidInput = [
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ];

    expect(fn () => $action->reset($user, $invalidInput))
        ->toThrow(ValidationException::class);
});
