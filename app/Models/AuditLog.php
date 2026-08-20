<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 👈 先頭の \ を除去して綺麗に
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory; // 👈 💡 これをクラスの内側に追加してください！これでデッドコードが解消されます。

    /**
     * 複数代入（Mass Assignment）を許可するカラムの定義
     */
    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
    ];

    /**
     * この監査ログが属するユーザー（逆方向のリレーション）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🔒 監査ログを簡単にデータベースへ記録するための共通静的メソッド
     */
    public static function log(string $event, int $userId, array $context = []): self
    {
        return self::create([
            'user_id'    => $userId,
            'event'      => $event,
            'ip_address' => Request::ip(),          // 💡 自動で接続元IPを取得
            'user_agent' => Request::userAgent(),   // 💡 自動でブラウザ/デバイス情報を取得
        ]);
    }
}
