<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\Platform\PlatformAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Operator authentication for the admin console. Always uses the 'platform'
 * guard explicitly — never the ERP App\Http\Controllers\AuthController and never
 * the default guard.
 */
class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('platform.auth.login');
    }

    public function login(Request $request, PlatformAudit $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // is_active is passed to the provider so a deactivated operator can never
        // authenticate, even with the correct password.
        $attempt = $credentials + ['is_active' => 1];

        // Remember-me is deliberately NOT honoured here. Laravel's recaller is a
        // 400-day credential that bypasses this form entirely — including its
        // rate limit — so a single stolen cookie would grant a year of access to
        // a console that can DROP every customer database. Operators log in.
        if (! Auth::guard('platform')->attempt($attempt)) {
            // Record the failure. Without this a brute-force attempt against a
            // publicly reachable console leaves no trace anywhere: the throttle
            // silently absorbs it and nobody ever finds out it happened.
            //
            // The email is stored as supplied so the audit trail shows what was
            // tried; the password is never touched.
            $audit->log('operator.login_failed', null, null, [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ], 'Échec de connexion : '.$credentials['email']);

            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $operator = Auth::guard('platform')->user();
        $operator->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $audit->log('operator.login', $operator, null, [], $operator->email);

        return redirect()->intended(route('platform.dashboard'));
    }

    public function logout(Request $request, PlatformAudit $audit): RedirectResponse
    {
        $operator = Auth::guard('platform')->user();

        if ($operator !== null) {
            $audit->log('operator.logout', $operator, null, [], $operator->email);
        }

        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
