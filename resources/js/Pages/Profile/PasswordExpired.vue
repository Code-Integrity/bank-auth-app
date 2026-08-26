<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// リアルタイム・バリデーションチェック用のロジック
const passwordRequirements = computed(() => {
    const p = form.password;
    return [
        { label: "最低12文字以上", valid: p.length >= 12 },
        { label: "英大文字を含む", valid: /[A-Z]/.test(p) },
        { label: "英小文字を含む", valid: /[a-z]/.test(p) },
        { label: "数字を含む", valid: /[0-9]/.test(p) },
        {
            label: "記号を含む（@$!%*?&など）",
            valid: /[`!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/.test(p),
        },
    ];
});

const submit = () => {
    form.post(route("user.password-expired.update"), {
        onFinish: () =>
            form.reset("current_password", "password", "password_confirmation"),
    });
};
</script>

<template>
    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <span class="font-bold text-red-600 dark:text-red-400"
                >【セキュリティ警告】</span
            >
            あなたのパスワードは有効期限（90日）を超過しています。システム全体の安全を確保するため、欧州銀行基準を満たす新しいパスワードへの更新が義務付けられています。
        </div>

        <form @submit.prevent="submit">
            <!-- 現在のパスワード -->
            <div>
                <InputLabel for="current_password" value="現在のパスワード" />
                <TextInput
                    id="current_password"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="current-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.current_password"
                />
            </div>

            <!-- 新しいパスワード -->
            <div class="mt-4">
                <InputLabel for="password" value="新しいパスワード" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />

                <!-- リアルタイム・チェック・インジケーター -->
                <div
                    class="mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-xs space-y-1"
                >
                    <p
                        class="font-semibold text-gray-700 dark:text-gray-300 mb-1"
                    >
                        パスワードポリシー要件：
                    </p>
                    <div
                        v-for="(req, index) in passwordRequirements"
                        :key="index"
                        class="flex items-center space-x-2"
                    >
                        <span
                            :class="
                                req.valid ? 'text-green-600' : 'text-red-500'
                            "
                            class="font-bold"
                        >
                            {{ req.valid ? "✓" : "✗" }}
                        </span>
                        <span
                            :class="
                                req.valid
                                    ? 'text-green-700 dark:text-green-400'
                                    : 'text-gray-500'
                            "
                        >
                            {{ req.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 確認用パスワード -->
            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="新しいパスワード（確認）"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    パスワードを更新してログイン
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
