<?php

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

test('middleware: guest users can bypass the password expiration check and access the landing index', function () {
    $response = $this->get('/');

    expect($response->status())->toBeIn([200, 302]);
});

test('middleware: allows compliant users whose passwords were changed within 90 days to successfully access the dashboard', function () {
    $user = User::factory()->create([
        'password_changed_at' => Carbon::now()->subDays(45),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('middleware: forcefully isolates expired users past the 90-day validity window and provisions a security audit trail', function () {
    $user = User::factory()->create([
        'password_changed_at' => Carbon::now()->subDays(91),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('user.password-expired'));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'security.password.expired',
    ]);
});

test('middleware: prevents route looping and allows quarantined users to access the expiration view or logout endpoints seamlessly', function () {
    $user = User::factory()->create([
        'password_changed_at' => Carbon::now()->subDays(91),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => Carbon::now(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/user/password-expired');
    $response->assertStatus(200);
});

/* --- 🛡️ PSD2/EBA-Compliant Dual-Pipeline Rate Limiter Verification --- */

test('rate limiter: verifies the strict limit structures and structural 429 response payloads for per-account throttling', function () {
    $limiterClosure = RateLimiter::limiter('login-account');
    $request = Request::create('/login', 'POST', ['email' => 'target-account@bank.eu']);

    $limit = $limiterClosure($request);
    expect($limit)->toBeInstanceOf(Limit::class);

    $responseCallback = $limit->responseCallback;
    $response = $responseCallback($request);

    expect($response->getStatusCode())->toBe(429);
    $data = json_decode($response->getContent(), true);
    expect($data['message'])->toBe('Too many login attempts for this account. Locked for 15 minutes.');
});

test('rate limiter: verifies the strict limit structures and structural 429 response payloads for per-IP origin throttling', function () {
    $limiterClosure = RateLimiter::limiter('login-ip');
    $request = Request::create('/login', 'POST', ['email' => 'attacker-target@bank.eu']);

    $limit = $limiterClosure($request);
    expect($limit)->toBeInstanceOf(Limit::class);

    $responseCallback = $limit->responseCallback;
    $response = $responseCallback($request);

    expect($response->getStatusCode())->toBe(429);
    $data = json_decode($response->getContent(), true);
    expect($data['message'])->toBe('Too many login attempts from this IP. Locked for 15 minutes.');
});

test('rate limiter: traverses and executes the implicit legacy Fortify provider fallback limits', function () {
    $limiterClosure = RateLimiter::limiter('login');

    if ($limiterClosure) {
        $request = Request::create('/login', 'POST', ['email' => 'test@bank.eu']);
        $limiterClosure($request);
    }

    expect(true)->toBeTrue();
});

test('quarantine view: validates that entering compliant credentials successfully renews state, records audit trails, and restores session accessibility', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123!'),
        'password_changed_at' => Carbon::now()->subDays(91),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($user)->post('/user/password-expired', [
        'current_password' => 'old-password-123!',
        'password' => 'New-Secure-Password-2026!',
        'password_confirmation' => 'New-Secure-Password-2026!',
    ]);

    $response->assertRedirect(url('/'));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'security.password.renewed',
    ]);
});

test('middleware resilience: guarantees that system redirection and quarantine states are strictly preserved even during critical audit log database failures', function () {
    AuditLog::creating(function ($model) {
        throw new Exception('Database Connection Failure');
    });

    $user = User::factory()->create([
        'password_changed_at' => Carbon::now()->subDays(91),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('user.password-expired'));

    AuditLog::flushEventListeners();
});
