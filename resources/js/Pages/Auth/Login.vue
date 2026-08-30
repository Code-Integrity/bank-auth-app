<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from "axios"; // 💡 確実な通信のために axios をインポート

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

// ⚡ Inertiaを迂回し、本番のロードバランサー摩擦を完全粉砕する力押し送信
const submit = async () => {
    if (form.processing) return;

    form.processing = true;
    form.clearErrors();

    try {
        // バックエンドに直接データを送信
        const response = await axios.post(route("login"), {
            email: form.email,
            password: form.password,
            remember: form.remember ? "on" : "",
        });

        // 🛡️ 物理的迂回ルート：戻ってきたJSONのURLへウィンドウレベルで強制ジャンプ！
        if (response.data && response.data.redirect) {
            window.location.href = response.data.redirect;
            return;
        }

        // 万が一、通常のリダイレクトHTMLが返ってきた場合のフォールバック
        window.location.reload();
    } catch (error) {
        // バリデーションエラー（422）や試行制限エラー（429）のハンドリング
        if (error.response && error.response.data) {
            const data = error.response.data;

            // Laravel標準のバリデーションエラーをフォームにマッピング
            if (data.errors) {
                form.setError(data.errors);
            }
            // レートリミッター（429）などのメッセージをemail欄に集約表示
            else if (data.message) {
                form.setError("email", data.message);
            }
        } else {
            form.setError(
                "email",
                "An unexpected error occurred. Please try again.",
            );
        }
    } finally {
        form.processing = false;
        form.reset("password"); // セキュリティのためパスワードは必ずクリア
    }
};
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

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
