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

// Real-time password requirement validation logic
const passwordRequirements = computed(() => {
    const p = form.password;
    return [
        { label: "At least 12 characters", valid: p.length >= 12 },
        { label: "Contains uppercase letters", valid: /[A-Z]/.test(p) },
        { label: "Contains lowercase letters", valid: /[a-z]/.test(p) },
        { label: "Contains numbers", valid: /[0-9]/.test(p) },
        {
            label: "Contains special characters (e.g., @$!%*?&)",
            valid: /[`!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/.test(p),
        },
    ];
});

const submit = () => {
    form.post(route("user.password-expired.update"), {
        errorBag: "updatePassword", // ★JetstreamのバリデーションエラーをInertiaのフォームに紐付け
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
                >[Security Alert]</span
            >
            Your password has expired (exceeded the 90-day validity period). To
            ensure system-wide security and comply with European banking
            standards, you are required to update your password.
        </div>

        <form @submit.prevent="submit">
            <!-- Current Password -->
            <div>
                <InputLabel for="current_password" value="Current Password" />
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

            <!-- New Password -->
            <div class="mt-4">
                <InputLabel for="password" value="New Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <!-- ✨ 修正後（こちらに差し替えてください） -->
                <InputError
                    class="mt-2"
                    :message="
                        $page.props.errors && $page.props.errors.updatePassword
                            ? $page.props.errors.updatePassword.password
                            : form.errors.password
                    "
                />

                <!-- Real-time Validation Indicator -->
                <div
                    class="mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-xs space-y-1"
                >
                    <p
                        class="font-semibold text-gray-700 dark:text-gray-300 mb-1"
                    >
                        Password Policy Requirements:
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

            <!-- Confirm New Password -->
            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm New Password"
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
                    Update Password & Log In
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
