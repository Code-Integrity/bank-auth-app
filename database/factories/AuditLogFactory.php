<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * ファクトリに対応するモデル名の指定
     */
    protected $model = AuditLog::class;

    /**
     * モデルのデフォルト状態（ダミーデータ）の定義
     */
    public function definition(): array
    {
        return [
            // user_idが指定されない場合、自動的にUserファクトリで親ユーザーを同時生成する設定
            'user_id'    => User::factory(),
            'event'      => 'user.login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
    }
}
