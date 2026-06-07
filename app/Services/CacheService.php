<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    protected int $defaultTTL = 3600; // 1 hour

    /**
     * Obtenir depuis cache ou exécuter callback
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? $this->defaultTTL, $callback);
    }

    /**
     * Invalider cache
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Invalider par pattern (Redis uniquement)
     */
    public function forgetByPattern(string $pattern): int
    {
        $keys = Redis::keys($pattern);
        
        if (empty($keys)) {
            return 0;
        }

        return Redis::del(...$keys);
    }

    /**
     * Invalider cache par tag
     */
    public function forgetByTag(string $tag): bool
    {
        return Cache::tags($tag)->flush();
    }

    /**
     * Cache dashboard stats
     */
    public function cacheDashboardStats(int $userId): array
    {
        return $this->remember("dashboard.stats.{$userId}", function () {
            // Logique pour calculer stats
            return [
                'total_sales' => \App\Models\Sale::sum('total_amount'),
                'total_purchases' => \App\Models\Purchase::sum('total_amount'),
                // etc...
            ];
        }, 600); // 10 minutes
    }

    /**
     * Cache produits
     */
    public function cacheProducts(): array
    {
        return $this->remember('products.all', function () {
            return \App\Models\Product::with('category', 'brand')->get()->toArray();
        });
    }

    /**
     * Nettoyer tout le cache
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Obtenir statistiques cache
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'keys_count' => count(Redis::keys('*')),
        ];
    }
}
