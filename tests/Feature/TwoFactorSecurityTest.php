<?php

use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Fortify;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);


uses(RefreshDatabase::class);

// 各テストを実行する前に、Viteのアセット読み込みエラーを無効化する
beforeEach(function () {
    Vite::spy();
});

it('redirects unauthenticated users to the login page', function () {
    // ログインしていない状態でダッシュボードにアクセスするとログイン画面に弾かれること
    get('/dashboard')
        ->assertRedirect('/login');
});

it('redirects authenticated users to profile page if 2FA is disabled', function () {
    // ログインはしているが、2FAが未設定のユーザーを作成
    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);

    // ダッシュボードに入ろうとすると、プロファイル（2FA設定）画面へ強制リダイレクトされること
    actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('profile.show'));
});

it('allows access to dashboard if 2FA is enabled', function () {
    // 2FA設定済みのユーザーを作成
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('test-secret-key'),
    ]);

    // 💡 正解：無事にダッシュボード（ステータス200）にアクセスできることをシンプルに検証します
    actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});

test('2FA未設定のユーザーでも、除外ルート（プロフィール画面やログアウト）にはアクセスできる', function () {
    // 1. 2FA未設定のユーザーを作成し、ログイン状態にする
    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);
    $this->actingAs($user);

    // 2. プロフィール表示ルート（除外ルート）へのリクエストが、リダイレクトされずに正常終了（200）することを確認
    // ※ルート名が 'profile.show' であることを想定しています
    $response = $this->get(route('profile.show'));
    $response->assertStatus(200);

    // 3. ログアウトルート（除外ルート）へのリクエストも、2FA未設定を理由に弾かれないことを確認
    // ※Jetstreamのログアウトは通常POSTリクエストです
    $response = $this->post(route('logout'));
    $response->assertRedirect('/'); // ログアウト後の一般的なリダイレクト先
});

test('2FA未設定のユーザーが、保護された通常ルートにアクセスした場合はプロフィール画面にリダイレクトされる', function () {
    // 1. 2FA未設定のユーザーを作成し、ログイン状態にする
    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);
    $this->actingAs($user);

    // 2. 通常の保護されたルート（例: dashboard）にアクセスする
    $response = $this->get(route('dashboard'));

    // 3. プロフィール画面（2FA設定画面）へ強制リダイレクトされることを確認
    $response->assertRedirect(route('profile.show'));
});

test('2FA未設定のユーザーでも、プロフィール画面（profile.show）にはアクセスできる（無限ループ防止）', function () {
    // 1. 2FA未設定のユーザーを作成してログイン
    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);

    // 2. プロフィール画面へアクセス（if文の前半条件 $request->routeIs('profile.show') を通過させる）
    $response = $this->actingAs($user)->get(route('profile.show'));

    // 3. リダイレクトされずに正常に画面が表示されることを検証
    $response->assertStatus(200);
});

test('2FA未設定のユーザーでも、ログアウト処理（logout）は実行できる（無限ループ防止）', function () {
    // 1. 2FA未設定のユーザーを作成してログイン
    $user = User::factory()->create([
        'two_factor_secret' => null,
    ]);

    // 2. ログアウト処理を実行（if文の後半条件 $request->is('logout') を通過させる）
    // ※JetstreamのログアウトルートはPOSTメソッド、URLパスは '/logout' です
    $response = $this->actingAs($user)->post('/logout');

    // 3. セッションが切れ、トップページ等に正しくリダイレクトされることを検証
    $response->assertRedirect('/');
    $this->assertGuest();
});

test('EnsureTwoFactorEnabledミドルウェア：除外ルート判定の完全網羅（単体テスト）', function () {
    // 1. ミドルウェアのインスタンス化
    $middleware = new EnsureTwoFactorEnabled();

    // 2. 2FA未設定のユーザーを用意
    $user = User::factory()->make(['two_factor_secret' => null]);

    // 次のミドルウェアの動きをするコールバック
    $next = function ($req) {
        return new Response('passed');
    };

    // ─── パターンA: profile.show の網羅 ───
    $requestA = Request::create('/user/profile', 'GET');
    $requestA->setUserResolver(fn() => $user);
    $routeMockA = tap(mock(\Illuminate\Routing\Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show')->andReturn(true);
    });
    $requestA->setRouteResolver(fn() => $routeMockA);
    $responseA = $middleware->handle($requestA, $next);
    expect($responseA->getContent())->toBe('passed');

    // ─── パターンB: logout の網羅 ───
    $requestB = Request::create('/logout', 'POST');
    $requestB->setUserResolver(fn() => $user);
    $routeMockB = tap(mock(\Illuminate\Routing\Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(false);
    });
    $requestB->setRouteResolver(fn() => $routeMockB);
    $responseB = $middleware->handle($requestB, $next);
    expect($responseB->getContent())->toBe('passed');

    // ─── パターンC: two-factor.*（2FA制御ルート）の網羅 ───
    $requestC = Request::create('/user/two-factor-authentication', 'POST');
    $requestC->setUserResolver(fn() => $user);
    $routeMockC = tap(mock(\Illuminate\Routing\Route::class), function ($mock) {
        // two-factor.* がヒットしたと仮定
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(true);
    });
    $requestC->setRouteResolver(fn() => $routeMockC);
    $responseC = $middleware->handle($requestC, $next);
    expect($responseC->getContent())->toBe('passed');

    // ─── パターンD: user/confirm-password（パスワード確認URL）の網羅 ───
    $requestD = Request::create('/user/confirm-password', 'GET');
    $requestD->setUserResolver(fn() => $user);
    $routeMockD = tap(mock(\Illuminate\Routing\Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(false);
    });
    $requestD->setRouteResolver(fn() => $routeMockD);
    $responseD = $middleware->handle($requestD, $next);
    expect($responseD->getContent())->toBe('passed');

    // ─── パターンE: user/confirmed-password-status の網羅 ───
    $requestE = Request::create('/user/confirmed-password-status', 'GET');
    $requestE->setUserResolver(fn() => $user);
    $routeMockE = tap(mock(\Illuminate\Routing\Route::class), function ($mock) {
        $mock->shouldReceive('named')->with('profile.show', 'logout', 'two-factor.*', 'password.confirm', 'password.confirmation')->andReturn(false);
    });
    $requestE->setRouteResolver(fn() => $routeMockE);
    $responseE = $middleware->handle($requestE, $next);
    expect($responseE->getContent())->toBe('passed');
});

test('FortifyServiceProvider：すべてのレートリミッター制限ロジックを網羅する', function () {
    // ─── 1. login レートリミッターの網羅 ───
    $this->post('/login', [
        Fortify::username() => 'test@example.com',
        'password' => 'password',
    ]);

    // ─── 2. two-factor レートリミッターの網羅 ───
    $this->post('/two-factor-challenge', [
        'code' => '123456',
    ]);

    // ─── 3. passkeys レートリミッターの網羅 ───
    $passkeysLimiter = RateLimiter::limiter('passkeys');
    expect($passkeysLimiter)->not->toBeNull();

    $requestPasskeyA = \Illuminate\Http\Request::create('/passkeys/callback', 'POST', [
        'credential' => ['id' => 'mock-credential-id']
    ]);
    $passkeysLimiter($requestPasskeyA);

    $requestPasskeyB = \Illuminate\Http\Request::create('/passkeys/callback', 'POST');
    $sessionMock = mock(\Illuminate\Contracts\Session\Session::class);
    $sessionMock->shouldReceive('getId')->andReturn('mock-session-id');
    $requestPasskeyB->setLaravelSession($sessionMock);

    $passkeysLimiter($requestPasskeyB);
});

test('2FA未設定ユーザーが隔離された際に監査ログが記録される', function () {
    // 1. 2FA未設定（two_factor_secretがnull）のユーザーを本物のDBに作成
    $user = \App\Models\User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);

    // 2. 隔離対象のルートへアクセスさせてミドルウェアを発火させる
    $this->actingAs($user)->get('/dashboard');

    // 3. 実際に本物のデータベース（audit_logsテーブル）に隔離ログが記録されたかを厳格に検証
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event'   => 'middleware.2fa.redirect', // 確定したカラム名 'event' に対応
    ]);
});
