<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import SectionBorder from "@/Components/SectionBorder.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <!-- 🛡️ 各コンポーネント間のHTML的な干渉（フォームの吸い込み）を100%完全に防ぐため、 -->
        <!-- すべてのコンポーネントを独立した「単独のdivブロック」として完全に分離・フラット化します！ -->
        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-12">
                <!-- 👑 BLOCK 1: プロフィール情報フォーム（元の位置の最上部へ配置） -->
                <div
                    v-if="$page.props.jetstream.canUpdateProfileInformation"
                    class="bg-transparent"
                >
                    <UpdateProfileInformationForm
                        :user="$page.props.auth.user"
                    />
                    <SectionBorder />
                </div>

                <!-- 👑 BLOCK 2: パスワード更新フォーム（隔離画面で100%動くため、通常画面からは安全に非表示化して干渉を完全遮断！） -->
                <div v-if="false" class="bg-transparent">
                    <UpdatePasswordForm />
                    <SectionBorder />
                </div>

                <!-- 👑 BLOCK 3: 2FAフォーム -->
                <div
                    v-if="
                        $page.props.jetstream.canManageTwoFactorAuthentication
                    "
                    class="bg-transparent"
                >
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                    />
                    <SectionBorder />
                </div>

                <!-- 👑 BLOCK 4: ブラウザセッション -->
                <div class="bg-transparent">
                    <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                </div>

                <!-- 👑 BLOCK 5: アカウント削除 -->
                <div
                    v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                    class="bg-transparent"
                >
                    <SectionBorder />
                    <div class="mt-10 sm:mt-0">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
