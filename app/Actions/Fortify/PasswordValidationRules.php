<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * パスワードを検証するためのバリデーションルールを取得（欧州銀行レベルのPSD2セキュリティ準拠）
     *
     * @return array<int, mixed>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            Password::min(12)         // 1. 最低12文字以上
                ->letters()           // 2. 文字（英字）を含む
                ->mixedCase()         // 3. 大文字と小文字を両方含む
                ->numbers()           // 4. 数字を最低1つ含む
                ->symbols()           // 5. 記号（!@#$%^&*など）を最低1つ含む
                ->uncompromised(),    // 6. 過去に漏洩が確認されているパスワードは拒否（Have I Been Pwned API連携）
            'confirmed',
        ];
    }
}
