<?php

use App\Models\User;

test('profile information can be updated', function () {

    $this->actingAs($user = User::factory()->create([
        'two_factor_secret' => encrypt('secret-key'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]));

    $this->put('/user/profile-information', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
    ]);

    expect($user->fresh())
        ->name->toEqual('Test Name')
        ->email->toEqual('test@example.com');
});
