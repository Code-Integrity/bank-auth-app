<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const page = usePage();
const errors = ref(
    page.props.errors?.updatePassword || page.props.errors || {},
);
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <!-- Left Column: Title and Description -->
        <div class="md:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium text-gray-900">
                    Update Password
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Ensure your account is using a long, random password to stay
                    secure.
                </p>
            </div>
        </div>

        <!-- Right Column: Form Layout Container -->
        <div class="mt-5 md:mt-0 md:col-span-2">
            <!-- Bypass Inertia entirely: Utilizing pure HTML form submission directly to the pre-verified POST endpoint to eliminate framework session route hijacking. -->
            <form
                method="POST"
                action="/user/password-expired"
                class="shadow overflow-hidden sm:rounded-md"
            >
                <!-- Safely embed the encrypted CSRF token for server-side state verification. -->
                <input
                    type="hidden"
                    name="_token"
                    :value="$page.props.csrf_token"
                />

                <!-- Fields Container (White Background Grid) -->
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                        <!-- Current Password -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel
                                for="current_password"
                                value="Current Password"
                            />
                            <div class="relative mt-1">
                                <TextInput
                                    id="current_password"
                                    name="current_password"
                                    :type="
                                        showCurrentPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    class="block w-full pr-10"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                    @click="
                                        showCurrentPassword =
                                            !showCurrentPassword
                                    "
                                >
                                    <svg
                                        v-if="showCurrentPassword"
                                        class="h-5 w-5"
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
                                        class="h-5 w-5"
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
                            <div
                                v-if="errors.current_password"
                                class="text-sm text-red-600 mt-2"
                            >
                                {{ errors.current_password }}
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="password" value="New Password" />
                            <div class="relative mt-1">
                                <TextInput
                                    id="password"
                                    name="password"
                                    :type="
                                        showNewPassword ? 'text' : 'password'
                                    "
                                    class="block w-full pr-10"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                    @click="showNewPassword = !showNewPassword"
                                >
                                    <svg
                                        v-if="showNewPassword"
                                        class="h-5 w-5"
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
                                        class="h-5 w-5"
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
                            <div
                                v-if="errors.password"
                                class="text-sm text-red-600 mt-2"
                            >
                                {{ errors.password }}
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel
                                for="password_confirmation"
                                value="Confirm Password"
                            />
                            <div class="relative mt-1">
                                <TextInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    :type="
                                        showConfirmPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    class="block w-full pr-10"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                    @click="
                                        showConfirmPassword =
                                            !showConfirmPassword
                                    "
                                >
                                    <svg
                                        v-if="showConfirmPassword"
                                        class="h-5 w-5"
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
                                        class="h-5 w-5"
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
                        </div>
                    </div>
                </div>

                <!-- Action Button Container (Faint Gray Background Area) -->
                <div
                    class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6"
                >
                    <!-- Execute native, high-speed, and secure browser-level form execution via type="submit". -->
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
