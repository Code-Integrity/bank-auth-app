import { defineConfig, devices } from "@playwright/test";

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
    // 初期設定の `e2e` から Laravel の階層に合うよう明示的に指定
    testDir: "./tests/e2e",

    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,

    // ターミナルで見やすい 'list' レポートを指定
    reporter: "list",

    /* Shared settings for all the projects below. */
    use: {
        /* 🔴 デモ環境（Laravel Sail や Railway）のURLを指定してください */
        baseURL: "http://localhost",

        /* 🎬 テスト実行時に動画を「常時録画」する設定を有効化 */
        video: "on",

        /* トレース情報も初回の失敗時に残す設定（デフォルト） */
        trace: "on-first-retry",
    },

    /* プロジェクト設定 */
    projects: [
        /*
         * 💡 ポートフォリオ用の動画撮影が目的のため、
         * 海外で最もシェアの高い Chromium (Desktop Chrome) のみに絞り、最速で回します。
         */
        {
            name: "chromium",
            use: { ...devices["Desktop Chrome"] },
        },
    ],
});
