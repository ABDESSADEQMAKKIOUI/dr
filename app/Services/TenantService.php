<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class TenantService
{
    public function list(array $filters = [])
    {
        $query = Tenant::with('owner', 'subscription');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Tenant
    {
        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'domain' => $data['domain'] ?? null,
            'owner_id' => $data['owner_id'],
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14), // 14 jours d'essai
        ]);

        // Créer utilisateur admin du tenant
        if (isset($data['admin_email'])) {
            $this->createTenantAdmin($tenant, $data);
        }

        return $tenant;
    }

    protected function createTenantAdmin(Tenant $tenant, array $data): User
    {
        $user = User::create([
            'tenant_id' => $tenant->id,
            'first_name' => $data['admin_first_name'],
            'last_name' => $data['admin_last_name'],
            'email' => $data['admin_email'],
            'password' => bcrypt($data['admin_password']),
            'is_active' => true,
        ]);

        // Assigner rôle Super Admin
        $superAdminRole = \App\Models\Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $user->roles()->attach($superAdminRole->id);
        }

        return $user;
    }

    public function suspend(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'suspended']);
        return $tenant;
    }

    public function activate(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'active']);
        return $tenant;
    }

    public function delete(Tenant $tenant): bool
    {
        // Soft delete
        return $tenant->delete();
    }

    public function getStats(): array
    {
        return [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'trial' => Tenant::where('trial_ends_at', '>', now())->count(),
        ];
    }
}
