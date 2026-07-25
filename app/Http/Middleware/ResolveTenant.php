<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use App\Services\SmtpSettings;
use App\Tenancy\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('tenancy.enabled')) {
            return $next($request);            // legacy single-tenant mode
        }

        $host  = strtolower($request->getHost());
        $root  = strtolower((string) config('tenancy.root_domain'));
        $admin = strtolower((string) config('tenancy.admin_domain'));

        // routes/platform.php is registered on Route::domain($admin) BEFORE
        // routes/web.php. Reaching this line on the admin host means no platform
        // route matched, i.e. an ERP URL on the operator console. Never resolve.
        if ($host === $admin) {
            abort(404);
        }

        $suffix = '.'.$root;
        if ($host === $root || ! str_ends_with($host, $suffix)) {
            return $this->reject($request, 'unknown', 404);
        }

        $slug = substr($host, 0, -strlen($suffix));

        if (str_contains($slug, '.')
            || ! preg_match(config('tenancy.slug_pattern'), $slug)
            || in_array($slug, config('tenancy.reserved_slugs'), true)) {
            return $this->reject($request, 'unknown', 404);
        }

        // ->on('platform') is explicit so this lookup can never depend on
        // whatever config('database.default') currently points at.
        $tenant = Tenant::on('platform')->where('slug', $slug)->first();

        if (! $tenant) {
            return $this->reject($request, 'unknown', 404);
        }

        // tenants.status is the SINGLE SOURCE OF TRUTH. This middleware performs
        // no date arithmetic and never reads subscriptions -- the scheduled
        // command platform:sync-subscriptions owns every status transition.
        switch ($tenant->status) {
            case Tenant::STATUS_ACTIVE:
                break;
            case Tenant::STATUS_SUSPENDED:
                return $this->reject($request, 'suspended', 503, $tenant);
            case Tenant::STATUS_EXPIRED:
                return $this->reject($request, 'expired', 503, $tenant);
            case Tenant::STATUS_PROVISIONING:
                return $this->reject($request, 'provisioning', 503, $tenant)
                    ->header('Retry-After', '30');
            default: // failed, archived, or any value added later
                return $this->reject($request, 'unknown', 404);
        }

        // Storage isolation MUST happen before StartSession builds its driver
        // and before anything resolves the cache repository.
        $this->isolateStorage($slug);

        // The connection swap.
        Tenancy::useTenant($tenant);

        // White-label the instance for this customer. config('app.name') is what
        // layouts/app.blade.php renders into <title> and what Laravel uses as the
        // default mail "from" name — without this, every customer's browser tab
        // and every invoice email says "SAFM Demo" instead of their own company.
        config(['app.name' => $tenant->name]);

        // AppServiceProvider::boot() no longer touches the database. SMTP
        // settings are applied HERE, after the swap, from this tenant's own
        // `settings` table. Failures are swallowed exactly as before.
        SmtpSettings::apply();

        $request->attributes->set('tenant', $tenant);
        app()->instance('tenancy.tenant', $tenant);

        return $next($request);
    }

    private function isolateStorage(string $slug): void
    {
        $key         = str_replace('-', '_', $slug);
        $sessionPath = storage_path('framework/sessions/'.$slug);
        $cachePath   = storage_path('framework/cache/'.$slug.'/data');

        foreach ([$sessionPath, $cachePath] as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }

        config([
            // Distinct cookie name so two tenants open in two tabs cannot stomp
            // each other. SESSION_DOMAIN stays null -> host-only cookies.
            'session.cookie'              => 'safm_'.$key.'_session',
            // The real control: a session id lifted from another tenant simply
            // is not present in this directory.
            'session.files'               => $sessionPath,
            'cache.prefix'                => 'safm_'.$key.'_',
            'cache.stores.file.path'      => $cachePath,
            'cache.stores.file.lock_path' => $cachePath,
        ]);

        // Illuminate\Cache\FileStore IGNORES cache.prefix entirely -- the PATH is
        // the only isolation that works. CacheManager memoises $stores['file'],
        // and 'cache.store' is a container singleton; both must be dropped or
        // the pre-swap store survives and serves tenant A's data to tenant B.
        Cache::purge('file');
        app()->forgetInstance('cache.store');
    }

    private function reject(Request $request, string $view, int $status, ?Tenant $tenant = null): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('tenancy.'.$view)], $status);
        }

        // resources/views/tenancy/*.blade.php are STANDALONE HTML. They must not
        // @extends any ERP layout: there is no usable tenant connection here and
        // the layouts run @can() and App\Models\Setting lookups on render.
        return response()->view('tenancy.'.$view, ['tenant' => $tenant], $status);
    }
}
