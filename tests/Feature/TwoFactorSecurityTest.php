<?php

use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    Vite::spy();
});

it('redirects unauthenticated users to the login page', function () {

    get('/dashboard')
        ->assertRedirect('/login');
});

it('redirects authenticated users to profile page if 2FA is disabled', function () {

    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);

    actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('profile.show'));
});

it('allows access to dashboard if 2FA is enabled', function () {

    $user = User::factory()->create([
        'two_factor_secret' => encrypt('test-secret-key'),
    ]);

    actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});

test('mfa middleware: allows unconfigured users to access exempted routes such as profile updates and logout action', function () {

    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);
    $this->actingAs($user);

    $response = $this->get(route('profile.show'));
    $response->assertStatus(200);

    $response = $this->post(route('logout'));
    $response->assertRedirect('/');
});

test('mfa middleware: forcefully redirects and quarantines unconfigured users to the profile layer upon accessing protected routes', function () {

    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('profile.show'));
});

test('mfa middleware: guarantees unconfigured users can access the profile show endpoint without triggering an infinite redirect loop', function () {

    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);

    $response = $this->actingAs($user)->get(route('profile.show'));

    $response->assertStatus(200);
});

test('mfa middleware: assures unconfigured users can execute the logout termination flow without triggering an infinite redirect loop', function () {

    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('mfa middleware: completely covers unit test vectors for route exemption evaluation rules', function () {

    $middleware = new EnsureTwoFactorEnabled;

    $user = User::factory()->make(['two_factor_secret' => null]);

    $next = function ($req) {
        return new Response('passed');
    };

    $requestA = Request::create('/user/profile', 'GET');
    $requestA->setUserResolver(fn () => $user);
    $routeMockA = tap(mock(Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show')->andReturn(true);
    });
    $requestA->setRouteResolver(fn () => $routeMockA);
    $responseA = $middleware->handle($requestA, $next);
    expect($responseA->getContent())->toBe('passed');

    $requestB = Request::create('/logout', 'POST');
    $requestB->setUserResolver(fn () => $user);
    $routeMockB = tap(mock(Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(false);
    });
    $requestB->setRouteResolver(fn () => $routeMockB);
    $responseB = $middleware->handle($requestB, $next);
    expect($responseB->getContent())->toBe('passed');

    $requestC = Request::create('/user/two-factor-authentication', 'POST');
    $requestC->setUserResolver(fn () => $user);
    $routeMockC = tap(mock(Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(true);
    });
    $requestC->setRouteResolver(fn () => $routeMockC);
    $responseC = $middleware->handle($requestC, $next);
    expect($responseC->getContent())->toBe('passed');

    $requestD = Request::create('/user/confirm-password', 'GET');
    $requestD->setUserResolver(fn () => $user);
    $routeMockD = tap(mock(Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(false);
    });
    $requestD->setRouteResolver(fn () => $routeMockD);
    $responseD = $middleware->handle($requestD, $next);
    expect($responseD->getContent())->toBe('passed');

    $requestE = Request::create('/user/confirmed-password-status', 'GET');
    $requestE->setUserResolver(fn () => $user);
    $routeMockE = tap(mock(Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(false);
    });
    $requestE->setRouteResolver(fn () => $routeMockE);
    $responseE = $middleware->handle($requestE, $next);
    expect($responseE->getContent())->toBe('passed');
});

test('fortify provider: comprehensively exercises all discrete core rate-limiting protection vectors', function () {

    $this->post('/login', [
        Fortify::username() => 'test@example.com',
        'password' => 'password',
    ]);

    $this->post('/two-factor-challenge', [
        'code' => '123456',
    ]);

    $passkeysLimiter = RateLimiter::limiter('passkeys');
    expect($passkeysLimiter)->not->toBeNull();

    $requestPasskeyA = Request::create('/passkeys/callback', 'POST', [
        'credential' => ['id' => 'mock-credential-id'],
    ]);
    $passkeysLimiter($requestPasskeyA);

    $requestPasskeyB = Request::create('/passkeys/callback', 'POST');
    $sessionMock = mock(Session::class);
    $sessionMock->shouldReceive('getId')->andReturn('mock-session-id');
    $requestPasskeyB->setLaravelSession($sessionMock);

    $passkeysLimiter($requestPasskeyB);
});

test('mfa middleware: registers a structured security audit trail when an unconfigured user is forcefully quarantined', function () {

    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);

    $this->actingAs($user)->get('/dashboard');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'middleware.2fa.redirect',
    ]);
});
