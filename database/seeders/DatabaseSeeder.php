<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // デフォルトのテストユーザー
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 【追記】デモサイト専用の一発隔離シミュレーション用シーダーを呼び出す
        $this->call(DemoEnvironmentSeeder::class);
    }
}
