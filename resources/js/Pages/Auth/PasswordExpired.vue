<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

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
        errorBag: "updatePassword",
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
                <div class="relative mt-1">
                    <TextInput
                        id="current_password"
                        v-model="form.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        required
                        autofocus
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        @click="showCurrentPassword = !showCurrentPassword"
                    >
                        <svg
                            v-if="showCurrentPassword"
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
                <InputError
                    class="mt-2"
                    :message="form.errors.current_password"
                />
            </div>

            <!-- New Password -->
            <div class="mt-4">
                <InputLabel for="password" value="New Password" />
                <div class="relative mt-1">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        @click="showNewPassword = !showNewPassword"
                    >
                        <svg
                            v-if="showNewPassword"
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
                <InputError
                    class="mt-2"
                    :message="
                        form.errors.password ||
                        $page.props.errorBags?.updatePassword?.password?.[0]
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
                <div class="relative mt-1">
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <svg
                            v-if="showConfirmPassword"
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
