<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import ActionSection from "@/Components/ActionSection.vue";
import DangerButton from "@/Components/DangerButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.processing = true;

    axios
        .delete("/user", {
            data: {
                password: form.password,
            },
            withCredentials: true,
        })
        .then((response) => {
            confirmingUserDeletion.value = false;
            form.reset();

            window.location.href = "/";
        })
        .catch((error) => {
            form.processing = false;
            if (error.response && error.response.data.errors) {
                form.errors = error.response.data.errors;
            } else {
                passwordInput.value.focus();
            }
        });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title> Delete Account </template>

        <template #description> Permanently delete your account. </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </div>

            <div class="mt-5">
                <!-- Trigger Button: Safely constructed as an isolated type="button" to prevent native form submission loops. -->
                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    @click.prevent="confirmUserDeletion"
                >
                    Delete Account
                </button>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title> Delete Account </template>

                <template #content>
                    Are you sure you want to delete your account? Once your
                    account is deleted, all of its resources and data will be
                    permanently deleted. Please enter your password to confirm
                    you would like to permanently delete your account.

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="Password"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />

                        <InputError
                            :message="form.errors.password"
                            class="mt-2"
                        />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <!-- Final Execution Button: Explicitly declared as type="button" to physically destroy any implicit browser-level form submission triggers. -->
                    <button
                        type="button"
                        class="ms-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click.prevent="deleteUser"
                    >
                        Delete Account
                    </button>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
