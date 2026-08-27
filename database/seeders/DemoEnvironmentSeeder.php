<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoEnvironmentSeeder extends Seeder
{
    /**
     * Run the database seeds for the global demo site.
     */
    public function run(): void
    {
        // 共通の安全なデフォルトパスワード（デモ用）
        $defaultPassword = 'Password123!';

        //---------------------------------------------------------
        // 1. パスワード90日有効期限切れ：一発隔離シミュレーション用
        //---------------------------------------------------------
        User::factory()->create([
            'name' => 'Expired Password Demo Client',
            'email' => 'expired@aegisbank.demo',
            'password' => Hash::make($defaultPassword),
            'password_changed_at' => Carbon::now()->subDays(91), // 90日制限を突破
            'two_factor_secret' => encrypt('DEMOSECRETKEY12345'), // 2FAはクリア済みの状態を作る
            'two_factor_recovery_codes' => encrypt(json_encode(['rec-1', 'rec-2'])),
            'email_verified_at' => Carbon::now(),
        ]);

        //---------------------------------------------------------
        // 2. 2FA未設定：ダッシュボード隔離シミュレーション用
        //---------------------------------------------------------
        User::factory()->create([
            'name' => 'No-2FA Restricted Client',
            'email' => 'no-2fa@aegisbank.demo',
            'password' => Hash::make($defaultPassword),
            'password_changed_at' => Carbon::now(), // パスワードは新鮮
            'two_factor_secret' => null,            // 2FA未設定（隔離対象）
            'two_factor_recovery_codes' => null,
            'email_verified_at' => Carbon::now(),
        ]);

        //---------------------------------------------------------
        // 3. 完全セキュア：正常ログイン・ダッシュボード閲覧用
        //---------------------------------------------------------
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
