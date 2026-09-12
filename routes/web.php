<?php

use App\Http\Controllers\AuditLogController;
use App\Models\AuditLog;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/user/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('/user/password-expired', function () {
        return Inertia::render('Auth/PasswordExpired');
    })->name('user.password-expired');

    Route::post('/user/password-expired', function (Request $request) {

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:12',

                Password::defaults()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.'], 'updatePassword');
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ])->save();

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'security.password.renewed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Inertia::location(url('/dashboard'));
    })->name('user.password-expired.update');
});
