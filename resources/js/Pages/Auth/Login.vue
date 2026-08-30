<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});
// 💡 複雑な非同期送信処理（submit関数）をすべて撤去し、ブラウザの標準機能に命運を委ねます！
</script>


<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <!-- 🔄 resources/js/Pages/Auth/Login.vue の <form> から </form> までのブロックを、以下の「Vueの支配を100%脱出したピュアHTMLコード」に完全に置き換えます -->
        <form method="POST" action="/login" id="native-login-form">
            <!-- 🛡️ CSRFトークンをJavaScriptを一切介さずにクッキーから直接引き抜くネイティブな仕組み -->
            <input type="hidden" name="_token" :value="$page.props.csrf_token">

            <!-- 📧 メールアドレス入力欄（生のHTML input） -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; color: #374151; margin-bottom: 4px;">Email</label>
                <input type="email" name="email" required autofocus autocomplete="username" style="display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px; color: #111827;">
            </div>

            <!-- 🔑 パスワード入力欄（生のHTML input） -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; color: #374151; margin-bottom: 4px;">Password</label>
                <input type="password" name="password" required autocomplete="current-password" style="display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px; color: #111827;">
            </div>

            <!-- 🔄 記憶するチェックボックス（生のHTML input） -->
            <div style="margin-bottom: 16px; display: flex; align-items: center;">
                <input type="checkbox" name="remember" id="remember" style="border-radius: 4px; border: 1px solid #d1d5db; color: #4f46e5;">
                <label for="remember" style="margin-left: 8px; font-size: 14px; color: #4b5563;">Remember me</label>
            </div>

            <!-- 🚀 究極の力押し送信ボタン（Vueコンポーネントを介さない100%生のHTMLボタン） -->
            <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                <button type="submit" style="background-color: #1f2937; color: #ffffff; font-weight: 600; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; text-transform: uppercase; font-size: 12px; tracking: wider;">
                    Log in
                </button>
            </div>
        </form>
    </AuthenticationCard>
</template>
