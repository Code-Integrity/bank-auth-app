<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // テスト用のダミーウェブルートを瞬時に定義
    Route::get('/_test_gate', function () {
        return 'passed';
    })->middleware(\App\Http\Middleware\DemoBasicAuth::class);
});

it('allows request to pass seamlessly when demo basic auth is disabled', function () {
    // 💡 1. 認証が無効化されているケース（通常開発・テスト環境）のテスト
    Config::set('app.demo_basic_auth_enabled', false);

    $response = $this->get('/_test_gate');

    $response->assertStatus(200);
    $response->assertSee('passed');
});

it('blocks unauthorized requests with a 401 basic auth headers when enabled', function () {
    // 💡 2. 認証が有効で、クレデンシャルがないケースのテスト
    Config::set('app.demo_basic_auth_enabled', true);
    Config::set('app.demo_basic_auth_user', 'aegis-guest');
    Config::set('app.demo_basic_auth_password', 'SecurePreview2026');

    $response = $this->get('/_test_gate');

    $response->assertStatus(401);
    $response->assertHeader('WWW-Authenticate');
});

it('allows authorized requests with correct credentials when enabled', function () {
    // 💡 3. 認証が有効で、正しいクレデンシャルが送られたケースのテスト
    Config::set('app.demo_basic_auth_enabled', true);
    Config::set('app.demo_basic_auth_user', 'aegis-guest');
    Config::set('app.demo_basic_auth_password', 'SecurePreview2026');

    // HTTP Basic認証ヘッダーを付与してリクエスト
    $response = $this->withHeaders([
        'Authorization' => 'Basic ' . base64_encode('aegis-guest:SecurePreview2026')
    ])->get('/_test_gate');

    $response->assertStatus(200);
    $response->assertSee('passed');
});
