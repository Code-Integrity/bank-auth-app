<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import ActionMessage from "@/Components/ActionMessage.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (form.processing) return;
    form.processing = true;
    form.clearErrors();

    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    const formData = new FormData();
    formData.append("_method", "PUT");
    formData.append("name", form.name);
    formData.append("email", form.email);
    if (form.photo) {
        formData.append("photo", form.photo);
    }

    axios
        .post(route("user-profile-information.update"), formData, {
            headers: {
                Accept: "application/json",
                "Content-Type": "multipart/form-data",
                "X-Requested-With": "XMLHttpRequest",
            },
        })
        .then((response) => {
            form.processing = false;

            router.reload({ only: ["auth"] });

            form.recentlySuccessful = true;
            setTimeout(() => (form.recentlySuccessful = false), 3000);
        })
        .catch((error) => {
            form.processing = false;
            if (
                error.response &&
                error.response.data &&
                error.response.data.errors
            ) {
                form.errors = error.response.data.errors;
            }
        });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route("current-user-photo.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <!-- Deprecate the FormSection component and switch to a pure div layout to physically eliminate any multi-form cross-contamination or browser Autofill event side-effects. -->
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <!-- Left Column: Title and Description -->
        <div class="md:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium text-gray-900">
                    Profile Information
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Update your account's profile information and email address.
                </p>
            </div>
        </div>

        <!-- Right Column: Form Layout Container -->
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div
                class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md"
            >
                <div class="grid grid-cols-6 gap-6">
                    <!-- Profile Photo -->
                    <div
                        v-if="$page.props.jetstream.managesProfilePhotos"
                        class="col-span-6 sm:col-span-4"
                    >
                        <input
                            id="photo"
                            ref="photoInput"
                            type="file"
                            class="hidden"
                            @change="updatePhotoPreview"
                        />
                        <InputLabel for="photo" value="Photo" />

                        <div v-show="!photoPreview" class="mt-2">
                            <img
                                :src="user.profile_photo_url"
                                :alt="user.name"
                                class="rounded-full size-20 object-cover"
                            />
                        </div>
                        <div v-show="photoPreview" class="mt-2">
                            <span
                                class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                                :style="
                                    'background-image: url(\'' +
                                    photoPreview +
                                    '\');'
                                "
                            />
                        </div>

                        <SecondaryButton
                            class="mt-2 me-2"
                            type="button"
                            @click.prevent="selectNewPhoto"
                        >
                            Select A New Photo
                        </SecondaryButton>
                        <SecondaryButton
                            v-if="user.profile_photo_path"
                            type="button"
                            class="mt-2"
                            @click.prevent="deletePhoto"
                        >
                            Remove Photo
                        </SecondaryButton>
                        <InputError :message="form.errors.photo" class="mt-2" />
                    </div>

                    <!-- Name -->
                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="name"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="username"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />

                        <div
                            v-if="
                                $page.props.jetstream.hasEmailVerification &&
                                user.email_verified_at === null
                            "
                        >
                            <p class="text-sm mt-2">
                                Your email address is unverified.
                                <button
                                    type="button"
                                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none"
                                    @click.prevent="sendEmailVerification"
                                >
                                    Click here to re-send the verification
                                    email.
                                </button>
                            </p>
                            <div
                                v-show="verificationLinkSent"
                                class="mt-2 font-medium text-sm text-green-600"
                            >
                                A new verification link has been sent to your
                                email address.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button Container (Faint Gray Background Area) -->
            <div
                class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md"
            >
                <ActionMessage :on="form.recentlySuccessful" class="me-3">
                    Saved.
                </ActionMessage>

                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click.prevent="updateProfileInformation"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</template>
