<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aliased as `role` in bootstrap/app.php.
 *
 * routes/system.php has always declared `role:super-admin` but the alias was
 * never registered, so those routes raised
 * "Target class [role] does not exist." instead of authorising anything.
 *
 * Role names are compared on a NORMALISED form: lower-cased with every run of
 * whitespace, underscores and hyphens folded to a single hyphen. This is what
 * makes `role:super-admin` match the seeded role literally named "Super Admin"
 * (RolePermissionSeeder). App\Models\User::hasRole() does a raw
 * LOWER(name) = ? comparison and would therefore never match the spaced form,
 * which is exactly why the routes must not rely on it.
 *
 * Several roles may be supplied and are OR-ed, in either supported form:
 *   role:super-admin,admin      role:super-admin|admin
 */
class EnsureErpRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Non authentifié.');
            }

            return redirect()->route('login');
        }

        // No role argument means "any authenticated ERP user".
        if ($roles === []) {
            return $next($request);
        }

        // Platform operators authenticate on the `platform` guard and are not
        // App\Models\User, so they hold no ERP role at all. Fail closed rather
        // than calling roles() on a foreign model.
        if (! $user instanceof User) {
            abort(403, 'Accès refusé.');
        }

        $required = [];

        foreach ($roles as $role) {
            foreach (preg_split('/[|,]/', $role) as $candidate) {
                $candidate = self::normalize((string) $candidate);

                if ($candidate !== '') {
                    $required[] = $candidate;
                }
            }
        }

        if ($required === []) {
            return $next($request);
        }

        $held = $user->roles()
            ->pluck('name')
            ->map(fn ($name) => self::normalize((string) $name))
            ->all();

        if (array_intersect($required, $held) === []) {
            abort(403, 'Accès refusé.');
        }

        return $next($request);
    }

    /**
     * Fold a role name to its comparable form: "Super Admin", "super_admin"
     * and "super-admin" all become "super-admin".
     */
    private static function normalize(string $value): string
    {
        return trim(
            (string) preg_replace('/[\s_-]+/u', '-', mb_strtolower(trim($value))),
            '-'
        );
    }
}
