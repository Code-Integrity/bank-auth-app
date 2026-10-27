<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SignedUrlAuthTest extends TestCase
{
    /** @test */
    public function test_visitors_can_access_the_redirect_route_and_get_signed_url()
    {

        $response = $this->get('/dev/generate-and-redirect');

        $response->assertStatus(302);
        $this->assertStringContainsString('/tester/welcome', $response->headers->get('Location'));
        $this->assertStringContainsString('signature=', $response->headers->get('Location'));
    }

    /** @test */
    public function test_valid_signed_url_issues_secure_cookie_and_bypasses_gate()
    {

        $signedUrl = URL::temporarySignedRoute('tester.welcome', now()->addMinutes(5));

        $response = $this->get($signedUrl);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertCookie('bypass_secure_gate');
    }

    /** @test */
    public function test_invalid_or_expired_signed_url_is_denied()
    {

        $invalidUrl = url('/tester/welcome?expires=12345&signature=invalid_hash');

        $response = $this->get($invalidUrl);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_basic_auth_gate_strictly_blocks_unauthorized_requests()
    {

        config(['app.demo_basic_auth_enabled' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Basic '.base64_encode('invalid-user:wrong-password'),
        ])->get('/');

        $response->assertStatus(401);
        $response->assertHeader('WWW-Authenticate');
    }

    /** @test */
    public function test_malformed_cookie_trigger_exception_and_falls_back_to_basic_auth()
    {

        config(['app.demo_basic_auth_enabled' => true]);

        $response = $this->withCookie('bypass_secure_gate', 'invalid_not_encrypted_string')
            ->get('/');

        $response->assertStatus(401);
    }
}
