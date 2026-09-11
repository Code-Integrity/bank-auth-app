<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Define the fillable attributes permitted for mass assignment.
     */
    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
    ];

    /**
     * Define the inverse relationship to retrieve the user associated with this audit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Global static helper method to streamline fluent database logging of audit trails.
     */
    public static function log(string $event, int $userId, array $context = []): self
    {
        return self::create([
            'user_id' => $userId,
            'event' => $event,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
