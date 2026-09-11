<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from "axios";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = async () => {
    if (form.processing) return;

    form.processing = true;
    form.clearErrors();

    try {
        const response = await axios.post(route("login"), {
            email: form.email,
            password: form.password,
            remember: form.remember ? "on" : "",
        });

        if (response.data && response.data.redirect) {
            window.location.href = response.data.redirect;
            return;
        }

        window.location.reload();
    } catch (error) {
        if (error.response && error.response.data) {
            const data = error.response.data;

            if (data.errors) {
                form.setError(data.errors);
            } else if (data.message) {
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
        form.reset("password");
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

        <!-- 🔄 resources/js/Pages/Auth/Login.vue の <form> から </form> までのブロックを、以下の「Vueの支配を100%脱出したピュアHTMLコード」に完全に置き換えます -->
        <form method="POST" action="/login" id="native-login-form">
            <!-- 🛡️ CSRFトークンをJavaScriptを一切介さずにクッキーから直接引き抜くネイティブな仕組み -->
            <input type="hidden" name="_token" :value="$page.props.csrf_token">

            <!-- 📧 メールアドレス入力欄（生のHTML input） -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; color: #374151; margin-bottom: 4px;">Email</label>
                <input type="email" name="email" required autofocus autocomplete="username" style="display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px; color: #111827;">
            </div>

            <!-- Password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <div class="relative mt-1">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        required
                        autocomplete="current-password"
                    />
                    <!-- Password visibility toggle button-->
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        @click="showPassword = !showPassword"
                    >
                        <svg
                            v-if="showPassword"
                            class="size-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"
                            />
                        </svg>
                        <svg
                            v-else
                            class="size-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
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
