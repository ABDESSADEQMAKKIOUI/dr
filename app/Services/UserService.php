<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Liste des utilisateurs avec filtres
     */
    public function list(array $filters = [])
    {
        $query = User::with('roles', 'warehouse');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['role_id'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('roles.id', $filters['role_id']);
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Créer un utilisateur
     */
    public function create(array $data): User
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'is_active' => $data['is_active'] ??true,
            ]);

            // Assigner rôles
            if (isset($data['role_ids'])) {
                $user->roles()->attach($data['role_ids']);
            }

            // Assigner entrepôts
            if (isset($data['warehouse_ids'])) {
                $user->warehouses()->attach($data['warehouse_ids']);
            }

            DB::commit();
            return $user->fresh('roles', 'warehouses');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(User $user, array $data): User
    {
        DB::beginTransaction();
        try {
            $updateData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            // Mettre à jour mot de passe si fourni
            if (isset($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            // Mettre à jour rôles
            if (isset($data['role_ids'])) {
                $user->roles()->sync($data['role_ids']);
            }

            // Mettre à jour entrepôts
            if (isset($data['warehouse_ids'])) {
                $user->warehouses()->sync($data['warehouse_ids']);
            }

            DB::commit();
            return $user->fresh('roles', 'warehouses');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function delete(User $user): bool
    {
        // Empêcher suppression du super admin
        if ($user->hasRole('Super Admin')) {
            throw new \Exception('Impossible de supprimer le Super Admin.');
        }

        return $user->delete();
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);

        return $user;
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Le mot de passe actuel est incorrect.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Historique des connexions
     */
    public function loginHistory(User $user, int $limit = 20)
    {
        return LoginHistory::where('user_id', $user->id)
            ->latest('logged_in_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Sessions actives
     */
    public function activeSessions(User $user)
    {
        return LoginHistory::where('user_id', $user->id)
            ->whereNull('logged_out_at')
            ->where('logged_in_at', '>=', now()->subDays(7))
            ->latest('logged_in_at')
            ->get();
    }

    /**
     * Déconnecter une session
     */
    public function destroySession(User $user, int $sessionId): void
    {
        LoginHistory::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->update(['logged_out_at' => now()]);
    }

    /**
     * Déconnecter toutes les sessions sauf la courante
     */
    public function destroyOtherSessions(User $user, int $currentSessionId): void
    {
        LoginHistory::where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now()]);

        // Révoquer tous les tokens sauf le courant
        $user->tokens()->where('id', '!=', $user->currentAccessToken()?->id)->delete();
    }
}
