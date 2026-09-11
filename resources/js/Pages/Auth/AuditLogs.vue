<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    logs: Object,
});

const getEventTitle = (event, fallbackDescription) => {
    switch (event) {
        case "auth.login.success":
            return "Successful Login";
        case "auth.login.failed":
            return "Failed Login Attempt";
        case "middleware.2fa.redirect":
            return "2FA Verification Redirect";
        case "user.password.updated":
            return "Password Successfully Updated";
        default:
            if (
                !fallbackDescription ||
                fallbackDescription.includes("Undefined")
            ) {
                return "Security Event Detected";
            }
            return fallbackDescription;
    }
};

const getEventClass = (event) => {
    switch (event) {
        case "auth.login.success":
            return "bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800";
        case "auth.login.failed":
            return "bg-red-100 text-red-800 border-red-200 animate-pulse dark:bg-red-900/30 dark:text-red-400 dark:border-red-800";
        case "middleware.2fa.redirect":
            return "bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800";
        default:
            return "bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700";
    }
};
</script>

<template>
    <AppLayout title="Security Audit Logs">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Security Audit Logs
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        This dashboard displays real-time records of critical
                        security events and operations associated with your
                        account. Please review this log regularly to ensure
                        there is no unauthorized access.
                    </p>

                    <!-- Timeline display component -->
                    <div
                        v-if="logs.data.length > 0"
                        class="relative border-l border-gray-200 dark:border-gray-700 ml-4 md:ml-6"
                    >
                        <div
                            v-for="log in logs.data"
                            :key="log.id"
                            class="mb-10 ml-6"
                        >
                            <!-- Timeline dot marker -->
                            <span
                                class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 dark:bg-blue-900/50 rounded-full -left-3 ring-8 ring-white dark:ring-gray-900"
                            >
                                <svg
                                    class="w-3 h-3 text-blue-800 dark:text-blue-400"
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

                            <!-- Log item card container -->
                            <div
                                class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm sm:flex sm:items-center sm:justify-between"
                            >
                                <div class="mb-2 sm:mb-0 w-full">
                                    <div
                                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex flex-wrap items-center gap-2"
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
                                            {{
                                                getEventTitle(
                                                    log.event,
                                                    log.description,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1 flex flex-wrap gap-x-4"
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

                    <!-- Fallback display for missing logs -->
                    <div
                        v-else
                        class="text-center py-12 text-gray-500 dark:text-gray-400"
                    >
                        No security events recorded yet.
                    </div>

                    <!-- Simple pagination links -->
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
                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border dark:border-gray-700 rounded"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :href="link.url"
                                    :class="[
                                        'mr-1 mb-1 px-4 py-3 text-sm leading-4 border dark:border-gray-700 rounded hover:bg-white dark:hover:bg-gray-800 focus:border-indigo-500 focus:text-indigo-500',
                                        link.active
                                            ? 'bg-indigo-600 text-white font-bold'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300',
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
