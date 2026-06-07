<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordService
{
    /**
     * Envoyer lien de réinitialisation
     */
    public function sendResetLink(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \Exception('Aucun utilisateur trouvé avec cet email.');
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new \Exception('Impossible d\'envoyer le lien de réinitialisation.');
        }

        return __($status);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function reset(array $credentials): string
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new \Exception(__($status));
        }

        return __($status);
    }

    /**
     * Changer le mot de passe (utilisateur connecté)
     */
    public function change(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Le mot de passe actuel est incorrect.');
        }

        // Vérifier que le nouveau mot de passe est différent
        if (Hash::check($newPassword, $user->password)) {
            throw new \Exception('Le nouveau mot de passe doit être différent de l\'ancien.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Révoquer tous les tokens sauf le courant
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', '!=', $currentToken?->id)->delete();
    }

    /**
     * Valider la force du mot de passe
     */
    public function validateStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }

        if (!preg_match('/[@$!%*?&]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial (@$!%*?&).';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculateStrength($password),
        ];
    }

    /**
     * Calculer la force du mot de passe (0-100)
     */
    protected function calculateStrength(string $password): int
    {
        $strength = 0;

        if (strlen($password) >= 8) $strength += 20;
        if (strlen($password) >= 12) $strength += 10;
        if (preg_match('/[A-Z]/', $password)) $strength += 15;
        if (preg_match('/[a-z]/', $password)) $strength += 15;
        if (preg_match('/[0-9]/', $password)) $strength += 20;
        if (preg_match('/[@$!%*?&]/', $password)) $strength += 20;

        return min(100, $strength);
    }
}
