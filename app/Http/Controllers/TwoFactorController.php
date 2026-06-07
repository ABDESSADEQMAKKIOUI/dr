<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor) {}

    // ──────────────────────────────────────────────
    // Setup page  GET /settings/security
    // ──────────────────────────────────────────────
    public function setup(): View
    {
        $user      = Auth::user();
        $enabled   = $this->twoFactor->isEnabled($user);
        $secret    = null;
        $qrUrl     = null;
        $recCodes  = [];

        if (!$enabled) {
            // Generate a fresh secret to display (not yet saved)
            $secret  = $this->twoFactor->generateSecret();
            $uri     = $this->twoFactor->getQrUri($user, $secret);
            $qrUrl   = $this->twoFactor->getQrDataUri($uri);
            session(['2fa_pending_secret' => $secret]);
        } else {
            $recCodes = $this->twoFactor->getRecoveryCodes($user);
        }

        return view('settings.security', compact('enabled', 'secret', 'qrUrl', 'recCodes'));
    }

    // ──────────────────────────────────────────────
    // Enable 2FA  POST /settings/security/enable
    // ──────────────────────────────────────────────
    public function enable(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $secret = session('2fa_pending_secret');

        if (!$secret || !$this->twoFactor->verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $this->twoFactor->enable(Auth::user(), $secret);
        session()->forget('2fa_pending_secret');

        return redirect()->route('settings.security')->with('success', 'Two-factor authentication has been enabled.');
    }

    // ──────────────────────────────────────────────
    // Disable 2FA  POST /settings/security/disable
    // ──────────────────────────────────────────────
    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);

        $this->twoFactor->disable(Auth::user());

        return redirect()->route('settings.security')->with('success', 'Two-factor authentication has been disabled.');
    }

    // ──────────────────────────────────────────────
    // Verify page  GET /auth/two-factor
    // (shown after login when 2FA is required)
    // ──────────────────────────────────────────────
    public function showVerify(): View
    {
        return view('auth.two-factor');
    }

    // ──────────────────────────────────────────────
    // Verify 2FA code  POST /auth/two-factor
    // ──────────────────────────────────────────────
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);

        $user   = Auth::user();
        $secret = $this->twoFactor->getSecret($user);
        $code   = preg_replace('/\s+/', '', $request->input('code'));

        // Check TOTP code
        if ($secret && $this->twoFactor->verify($secret, $code)) {
            session(['2fa_verified' => true]);
            return redirect()->intended('dashboard');
        }

        // Check recovery code
        $recCodes = $this->twoFactor->getRecoveryCodes($user);
        if (in_array(strtoupper($code), array_map('strtoupper', $recCodes))) {
            // Burn the used recovery code
            $remaining = array_values(array_filter($recCodes, fn($c) => strtoupper($c) !== strtoupper($code)));
            $user->update(['two_factor_recovery_codes' => encrypt(json_encode($remaining))]);
            session(['2fa_verified' => true]);
            return redirect()->intended('dashboard');
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }
}
