<?php

use App\Http\Middleware\DemoBasicAuth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

beforeEach(function () {

    Route::get('/_test_gate', function () {
        return 'passed';
    })->middleware(DemoBasicAuth::class);
});

it('allows request to pass seamlessly when demo basic auth is disabled', function () {

    Config::set('app.demo_basic_auth_enabled', false);

    $response = $this->get('/_test_gate');

    $response->assertStatus(200);
    $response->assertSee('passed');
});

it('blocks unauthorized requests with a 401 basic auth headers when enabled', function () {

    Config::set('app.demo_basic_auth_enabled', true);
    Config::set('app.demo_basic_auth_user', 'aegis-guest');
    Config::set('app.demo_basic_auth_password', 'SecurePreview2026');

    $response = $this->get('/_test_gate');

    $response->assertStatus(401);
    $response->assertHeader('WWW-Authenticate');
});

it('allows authorized requests with correct credentials when enabled', function () {

    Config::set('app.demo_basic_auth_enabled', true);
    Config::set('app.demo_basic_auth_user', 'aegis-guest');
    Config::set('app.demo_basic_auth_password', 'SecurePreview2026');

    $response = $this->withHeaders([
        'Authorization' => 'Basic '.base64_encode('aegis-guest:SecurePreview2026'),
    ])->get('/_test_gate');

    $response->assertStatus(200);
    $response->assertSee('passed');
});
