<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the specific model class associated with this factory instance.
     */
    protected $model = AuditLog::class;

    /**
     * Define the default structural state and mock properties for the model factory.
     */
    public function definition(): array
    {
        return [

            'user_id' => User::factory(),
            'event' => 'user.login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
    }
}
