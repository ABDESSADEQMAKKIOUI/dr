<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Liste des rôles
     */
    public function list()
    {
        return Role::with('permissions')->withCount('users')->get();
    }

    /**
     * Créer un rôle
     */
    public function create(array $data): Role
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'description' => $data['description'] ?? null,
                'is_default' => $data['is_default'] ?? false,
            ]);

            // Si is_default = true, mettre tous les autres à false
            if ($role->is_default) {
                Role::where('id', '!=', $role->id)->update(['is_default' => false]);
            }

            // Assigner permissions
            if (isset($data['permission_ids'])) {
                $role->permissions()->attach($data['permission_ids']);
            }

            DB::commit();
            return $role->fresh('permissions');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Role $role, array $data): Role
    {
        DB::beginTransaction();
        try {
            // Empêcher modification du Super Admin
            if ($role->name === 'Super Admin') {
                throw new \Exception('Impossible de modifier le rôle Super Admin.');
            }

            $role->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_default' => $data['is_default'] ?? false,
            ]);

            // Si is_default = true, mettre tous les autres à false
            if ($role->is_default) {
                Role::where('id', '!=', $role->id)->update(['is_default' => false]);
            }

            // Mettre à jour permissions
            if (isset($data['permission_ids'])) {
                $role->permissions()->sync($data['permission_ids']);
            }

            DB::commit();
            return $role->fresh('permissions');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer un rôle
     */
    public function delete(Role $role): bool
    {
        // Empêcher suppression du Super Admin
        if ($role->name === 'Super Admin') {
            throw new \Exception('Impossible de supprimer le rôle Super Admin.');
        }

        // Vérifier si des utilisateurs ont ce rôle
        if ($role->users()->count() > 0) {
            throw new \Exception('Impossible de supprimer un rôle assigné à des utilisateurs.');
        }

        return $role->delete();
    }

    /**
     * Assigner des permissions à un rôle
     */
    public function assignPermissions(Role $role, array $permissionIds): Role
    {
        // Empêcher modification du Super Admin
        if ($role->name === 'Super Admin') {
            throw new \Exception('Impossible de modifier les permissions du Super Admin.');
        }

        $role->permissions()->sync($permissionIds);
        return $role->fresh('permissions');
    }

    /**
     * Liste des permissions groupées
     */
    public function permissionsGrouped()
    {
        return Permission::all()->groupBy('group');
    }

    /**
     * Vérifier si un utilisateur a une permission
     */
    public function userHasPermission(int $userId, string $permissionName): bool
    {
        $user = \App\Models\User::find($userId);
        return $user ? $user->hasPermission($permissionName) : false;
    }

    /**
     * Vérifier si un utilisateur a un rôle
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        $user = \App\Models\User::find($userId);
        return $user ? $user->hasRole($roleName) : false;
    }
}
