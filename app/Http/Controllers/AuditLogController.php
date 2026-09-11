<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    /**
     * Display the index of audit logs belonging to the authenticated user.
     */
    public function index(Request $request)
    {
        $logs = $request->user()->auditLogs()
            ->latest()
            ->paginate(10)
            ->through(fn ($log) => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $this->formatEventDescription($log->event),
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->isoFormat('YYYY-MM-DD HH:mm:ss'),
            ]);

        return Inertia::render('Auth/AuditLogs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Convert the audit log event types into human-readable, user-friendly labels.
     */
    private function formatEventDescription(string $event): string
    {
        return match ($event) {
            'auth.login.success' => 'Successful authentication and session established.',
            'auth.login.failed' => 'Failed authentication attempt (potential unauthorized entry vector detected).',
            'middleware.2fa.redirect' => 'Multi-factor authentication (MFA) enforcement quarantine triggered.',
            default => 'Unspecified or customized fallback security event payload.',
        };
    }
}
