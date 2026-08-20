<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // 💡 Userモデルと紐付ける外部キー（Userが削除されたらログも消える設定）
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 💡 セキュリティイベント名（例: auth.login.success）
            $table->string('event');
            // 💡 接続元のIPアドレス（IPv6も考慮して長さを設定）
            $table->string('ip_address', 45)->nullable();
            // 💡 接続元のブラウザ/デバイス情報（長文になるためテキスト型）
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
