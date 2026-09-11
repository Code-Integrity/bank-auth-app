<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (! Auth::check()) {
            return $next($request);
        }

        if (
            $request->routeIs('user.password-expired') ||
            $request->routeIs('user-password.update') ||
            $request->routeIs('user.password-expired.update') ||
            $request->routeIs('logout') ||
            $request->is('user/password-expired*')
        ) {
            return $next($request);
        }

        $user = Auth::user();

        $passwordChangedAt = $user->password_changed_at;
        $lastChanged = $passwordChangedAt ? Carbon::parse($passwordChangedAt) : now();

        if ($lastChanged->copy()->addDays(90)->isPast()) {

            try {

                AuditLog::create([
                    'user_id' => $user->id,
                    'event' => 'security.password.expired',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload' => json_encode(['last_changed_at' => $lastChanged->toIso8601String()]),
                ]);
            } catch (\Exception $e) {

                \Log::error('Audit log failed during password expiration: '.$e->getMessage());
            }

            return Inertia::location(route('user.password-expired'));
        }

        return $next($request);
    }
}
