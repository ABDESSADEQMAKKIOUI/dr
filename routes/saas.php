<?php

// Intentionally empty. Tenant and subscription management now lives in the
// operator console: routes/platform.php + App\Http\Controllers\Platform\*,
// which run on the `platform` guard and the `platform` database connection.
//
// This file used to declare /api/saas/* and /api/subscription/* against
// App\Http\Controllers\TenantController and SubscriptionController. Both
// resolved App\Models\Tenant -- a model that never existed -- and the tenant
// group was guarded by a `tenant.scope` middleware alias that was never
// registered. Every one of those endpoints was dead on arrival, and row-level
// tenant_id scoping is precisely the design that database-per-tenant replaced.
//
// The file itself must keep existing: routes/api.php requires it
// unconditionally, so deleting it would fatal the whole API route file.
