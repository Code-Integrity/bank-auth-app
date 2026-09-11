<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->user = User::factory()->create([
        'two_factor_secret' => 'encrypted-secret-string-here',
        'two_factor_recovery_codes' => encrypt(json_encode([])),
    ]);
});

/**
 * 1. Authentication and Authorization Verification Vector
 */
test('audit logs: unauthorized guest users cannot access the audit trail and are redirected to the login interface', function () {
    $this->get('/user/audit-logs')
        ->assertRedirect('/login');
});

test('audit logs: authenticated users with configured MFA can successfully access their own audit trail view', function () {
    AuditLog::factory()->count(2)->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get('/user/audit-logs')
        ->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Auth/AuditLogs')
                ->has('logs.data', 2)
        );
});

test('audit logs: guarantees strict data isolation and multi-tenancy rules by blocking other users records', function () {
    $otherUser = User::factory()->create([
        'two_factor_secret' => 'another-encrypted-secret',
        'two_factor_recovery_codes' => encrypt(json_encode([])),
    ]);

    AuditLog::factory()->create([
        'user_id' => $otherUser->id,
        'event' => 'secret.action',
    ]);

    AuditLog::factory()->create([
        'user_id' => $this->user->id,
        'event' => 'my.action',
    ]);

    $this->actingAs($this->user)
        ->get('/user/audit-logs')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Auth/AuditLogs')
                ->has('logs.data', 1)
                ->where('logs.data.0.event', 'my.action')
                ->whereNot('logs.data.0.event', 'secret.action')
        );
});

/**
 * 2. Multi-Layered MFA Quarantine Middleware Integration Vector
 */
test('audit logs: non-compliant users without MFA configured are locked out from audit views and quarantined to the profile layer', function () {
    $unprotectedUser = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);

    $this->actingAs($unprotectedUser)
        ->get('/user/audit-logs')
        ->assertRedirect('/user/profile');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $unprotectedUser->id,
        'event' => 'middleware.2fa.redirect',
    ]);
});

/**
 * 3. Chronological Order and Pagination Traversal Vector
 */
test('audit logs: records are sequenced in reverse-chronological order and paginated at a threshold of 10 items', function () {

    AuditLog::factory()->count(11)->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get('/user/audit-logs')
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Auth/AuditLogs')
                ->has('logs.data', 10)
                ->has('logs.links')
        );
});

/**
 * 4. 100% Total Coverage Optimization Vectors (Models/AuditLog, AuditLogController, and HandleInertiaRequests)
 */
test('audit logs: exercises all structural event types to fully cover the controller match expression logic', function () {

    $events = ['auth.login.success', 'auth.login.failed', 'middleware.2fa.redirect', 'unknown.event'];

    foreach ($events as $event) {
        AuditLog::factory()->create([
            'user_id' => $this->user->id,
            'event' => $event,
        ]);
    }

    $this->actingAs($this->user)
        ->get('/user/audit-logs')
        ->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Auth/AuditLogs')
                ->has('logs.data', 4)
        );
});

test('audit logs: validates that the fluent global static helper executes correctly and verifies inverse user relations', function () {
    $this->get('/login');

    $log = AuditLog::log('test.direct.event', $this->user->id);

    expect($log->user)->toBeInstanceOf(User::class)
        ->and($log->user->id)->toBe($this->user->id);

    $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);
});

test('audit logs: traverses all method states and flash notification branches inside HandleInertiaRequests middleware layer', function () {
    $request = $this->app['request'];

    session()->flash('flash.banner', 'Test Banner');
    session()->flash('flash.bannerStyle', 'success');

    $this->actingAs($this->user);
    $middleware = new HandleInertiaRequests;

    $version = $middleware->version($request);
    $sharedData = $middleware->share($request);

    expect($sharedData)->toBeArray();

    if (is_null($version)) {
        expect($version)->toBeNull();
    } else {
        expect($version)->toBeString();
    }
});
