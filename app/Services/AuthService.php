<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Inscrire un nouvel utilisateur
     */
    public function register(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        // Assigner le rôle par défaut (Caissier)
        if (isset($data['role_id'])) {
            $user->roles()->attach($data['role_id']);
        } else {
            $defaultRole = \App\Models\Role::where('is_default', true)->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id);
            }
        }

        // Envoyer email de vérification
        $user->sendEmailVerificationNotification();

        return $user->fresh('roles');
    }

    /**
     * Connexion utilisateur
     */
    public function login(array $credentials, bool $remember = false): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // Logger tentative échouée
            $this->logLogin($user, 'failed');
            
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est désactivé.'],
            ]);
        }

        // Vérifier si 2FA est activé
        if ($user->two_factor_secret) {
            return [
                'requires_2fa' => true,
                'user_id' => $user->id,
            ];
        }

        // Connexion réussie
        Auth::login($user, $remember);
        
        // Logger connexion
        $loginHistory = $this->logLogin($user, 'success');

        // Générer token API si nécessaire
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('roles.permissions'),
            'token' => $token,
            'login_history_id' => $loginHistory->id,
        ];
    }

    /**
     * Vérifier code 2FA et finaliser connexion
     */
    public function verify2FA(int $userId, string $code): array
    {
        $user = User::findOrFail($userId);

        // TODO: Vérifier le code 2FA (Google Authenticator)
        // Pour l'instant, on simule la vérification
        
        Auth::login($user);
        $loginHistory = $this->logLogin($user, 'success');
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('roles.permissions'),
            'token' => $token,
            'login_history_id' => $loginHistory->id,
        ];
    }

    /**
     * Déconnexion
     */
    public function logout(?int $loginHistoryId = null): void
    {
        if ($loginHistoryId) {
            LoginHistory::where('id', $loginHistoryId)
                ->update(['logged_out_at' => now()]);
        }

        // Révoquer tous les tokens
        Auth::user()?->tokens()->delete();
        
        Auth::logout();
    }

    /**
     * Logger une connexion
     */
    protected function logLogin(?User $user, string $status): ?LoginHistory
    {
        if (!$user) {
            return null;
        }

        return LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device' => $this->parseDevice(request()->userAgent()),
            'status' => $status,
            'logged_in_at' => now(),
        ]);
    }

    /**
     * Parser le device depuis user agent
     */
    protected function parseDevice(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        }
        return 'Desktop';
    }

    /**
     * Renvoyer email de vérification
     */
    public function resendVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new \Exception('Email déjà vérifié.');
        }

        $user->sendEmailVerificationNotification();
    }

    /**
     * Vérifier email
     */
    public function verifyEmail(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new \Exception('Email déjà vérifié.');
        }

        $user->markEmailAsVerified();
    }
}
