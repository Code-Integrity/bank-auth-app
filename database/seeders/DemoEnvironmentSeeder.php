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
        // 💡 12文字以上の要件を満たす金融グレードの共通公開ダミーパスワードへ最適化
        $defaultPassword = 'AegisDemo@Password2026';

        // -----------------------------------------------------------------------------------------
        // 1. Password Expired Case: Forcing structural quarantine validation (90-day policy breach)
        // -----------------------------------------------------------------------------------------
        User::factory()->create([
            'name' => 'Expired Password Demo Client',
            'email' => 'expired@aegisbank.demo',
            'password' => Hash::make($defaultPassword), // 安全にハッシュ化して格納
            'password_changed_at' => Carbon::now()->subDays(95), // 90日経過を確実にシミュレート
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
        // 3. Fully Compliant Case: Structurally aligned for explicit validation logging
        // ----------------------------------------------------------------------------------------
        User::factory()->create([
            'name' => 'Fully Secured Active Client',
            'email' => 'secured@aegisbank.demo',
            'password' => Hash::make($defaultPassword),
            // 💡 履歴の整合性と数日前の安定稼働ステートを完全に取り戻すため、
            // タイムスタンプ条件を元の安全なパラメータ（隔離・監査ログを優先させる設定）へ完全に復元します
            'password_changed_at' => Carbon::now(),
            'two_factor_secret' => encrypt('DEMOSECRETKEY67890'),
            'two_factor_recovery_codes' => encrypt(json_encode(['rec-3', 'rec-4'])),
            'email_verified_at' => Carbon::now(),
        ]);
    }
}
