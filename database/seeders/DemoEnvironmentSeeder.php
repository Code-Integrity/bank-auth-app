<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoEnvironmentSeeder extends Seeder
{
    /**
     * Run the database seeds for the global demo site.
     */
    public function run(): void
    {

        $defaultPassword = 'Password123!';

        // -----------------------------------------------------------------------------------------
        // 1. Password Expired Case: Forcing structural quarantine validation (90-day policy breach)
        // -----------------------------------------------------------------------------------------
        User::factory()->create([
            'name' => 'Expired Password Demo Client',
            'email' => 'expired@aegisbank.demo',
            'password' => Hash::make($defaultPassword),
            'password_changed_at' => Carbon::now()->subDays(91),
            'two_factor_secret' => encrypt('DEMOSECRETKEY12345'),
            'two_factor_recovery_codes' => encrypt(json_encode(['rec-1', 'rec-2'])),
            'email_verified_at' => Carbon::now(),
        ]);

        // ----------------------------------------------------------------------------------------
        // 2. MFA Unconfigured Case: Triggering absolute dashboard isolation verification
        // ----------------------------------------------------------------------------------------
        User::factory()->create([
            'name' => 'No-2FA Restricted Client',
            'email' => 'no-2fa@aegisbank.demo',
            'password' => Hash::make($defaultPassword),
            'password_changed_at' => Carbon::now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'email_verified_at' => Carbon::now(),
        ]);

        // ----------------------------------------------------------------------------------------
        // 3. Fully Compliant Case: Securing successful dashboard accessibility matrix
        // ----------------------------------------------------------------------------------------
        User::factory()->create([
            'name' => 'Fully Secured Active Client',
            'email' => 'secured@aegisbank.demo',
            'password' => Hash::make($defaultPassword),
            'password_changed_at' => Carbon::now(),
            'two_factor_secret' => encrypt('DEMOSECRETKEY67890'),
            'two_factor_recovery_codes' => encrypt(json_encode(['rec-3', 'rec-4'])),
            'email_verified_at' => Carbon::now(),
        ]);
    }
}
