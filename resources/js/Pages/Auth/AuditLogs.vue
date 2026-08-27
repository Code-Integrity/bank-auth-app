<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3"; // 💡 この行を追加してください！

defineProps({
    logs: Object,
});

// イベントごとのバッジ配色を決定する関数
const getEventClass = (event) => {
    switch (event) {
        case "auth.login.success":
            return "bg-green-100 text-green-800 border-green-200";
        case "auth.login.failed":
            return "bg-red-100 text-red-800 border-red-200 animate-pulse";
        case "middleware.2fa.redirect":
            return "bg-yellow-100 text-yellow-800 border-yellow-200";
        default:
            return "bg-gray-100 text-gray-800 border-gray-200";
    }
};
</script>

<template>
    <AppLayout title="セキュリティ監査ログ">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                セキュリティ監査ログ
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <p class="text-sm text-gray-600 mb-6">
                        ※この画面には、お使いのアカウントで行われたセキュリティ関連の重要な操作・イベントがリアルタイムに記録されています。不審なログイン履歴がないか定期的にご確認ください。
                    </p>

                    <!-- タイムラインコンポーネント -->
                    <div
                        v-if="logs.data.length > 0"
                        class="relative border-l border-gray-200 ml-4 md:ml-6"
                    >
                        <div
                            v-for="log in logs.data"
                            :key="log.id"
                            class="mb-10 ml-6"
                        >
                            <!-- タイムラインのドットマーカー -->
                            <span
                                class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white"
                            >
                                <svg
                                    class="w-3 h-3 text-blue-800"
                                    xmlns="http://w3.org"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286z"
                                    />
                                </svg>
                            </span>

                            <!-- ログカードの本体 -->
                            <div
                                class="p-4 bg-gray-50 rounded-lg border border-gray-100 shadow-sm sm:flex sm:items-center sm:justify-between"
                            >
                                <div class="mb-2 sm:mb-0">
                                    <div
                                        class="text-sm font-semibold text-gray-700 flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="text-xs font-mono text-gray-400"
                                            >[{{ log.created_at }}]</span
                                        >
                                        <span
                                            :class="[
                                                'px-2.5 py-0.5 rounded-full text-xs font-medium border',
                                                getEventClass(log.event),
                                            ]"
                                        >
                                            {{ log.description }}
                                        </span>
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 font-mono mt-1 flex flex-wrap gap-x-4"
                                    >
                                        <span
                                            ><strong>IP:</strong>
                                            {{ log.ip_address }}</span
                                        >
                                        <span
                                            class="truncate max-w-xs md:max-w-md"
                                            :title="log.user_agent"
                                        >
                                            <strong>UA:</strong>
                                            {{ log.user_agent }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ログが存在しない場合の空表示 -->
                    <div v-else class="text-center py-12 text-gray-500">
                        記録されたセキュリティイベントはまだありません。
                    </div>

                    <!-- ページネーション（簡易版リンク） -->
                    <div
                        v-if="logs.links && logs.links.length > 3"
                        class="mt-6 flex justify-center"
                    >
                        <nav class="flex gap-1">
                            <template
                                v-for="(link, key) in logs.links"
                                :key="key"
                            >
                                <div
                                    v-if="link.url === null"
                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :href="link.url"
                                    :class="[
                                        'mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500',
                                        link.active
                                            ? 'bg-indigo-600 text-white font-bold'
                                            : 'bg-white text-gray-700',
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
