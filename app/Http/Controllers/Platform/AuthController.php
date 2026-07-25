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

        if (! Auth::guard('platform')->attempt($attempt, $request->boolean('remember'))) {
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
