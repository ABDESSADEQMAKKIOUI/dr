<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->tenant_id) {
            return response()->json(['error' => 'Tenant non trouvé'], 403);
        }

        // Ajouter tenant_id dans les requêtes Eloquent
        \App\Models\Product::addGlobalScope('tenant', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        });

        \App\Models\Customer::addGlobalScope('tenant', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        });

        \App\Models\Sale::addGlobalScope('tenant', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        });

        // Ajouter pour tous les autres models...

        return $next($request);
    }
}
