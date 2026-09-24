<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoBasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (config('app.demo_basic_auth_enabled', false)) {
            $username = config('app.demo_basic_auth_user', 'aegis-guest');
            $password = config('app.demo_basic_auth_password', 'SecurePreview2026');

            if ($request->getUser() !== $username || $request->getPassword() !== $password) {
                return response('Unauthorized.', 401, [
                    'WWW-Authenticate' => 'Basic realm="AegisBank Auth Core Demo", charset="UTF-8"',
                ]);
            }
        }

        return $next($request);
    }
}
