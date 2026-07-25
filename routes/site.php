<?php

use App\Http\Controllers\Site\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site (the apex host)
|--------------------------------------------------------------------------
|
| Registered in bootstrap/app.php under Route::domain(config('tenancy.root_domain'))
| with the 'site' middleware group and the 'site.' name prefix, BEFORE the
| unconstrained tenant web group. Consequences to keep in mind when editing:
|
|   - Every route here is UNAUTHENTICATED and reachable by anyone on the
|     internet. Anything with side effects needs a throttle.
|   - ResolveTenant never runs on these routes, so there is NO tenant database.
|     Only platform-pinned models (App\Models\Platform\*) may be queried.
|   - The domain constraint is an exact host match, so acme.facturation.cfpss.ma
|     and admin.facturation.cfpss.ma are untouched by this file.
|
*/

Route::get('/', [LandingController::class, 'index'])->name('home');

// Demo / contact request. Public, unauthenticated, writes to the database:
// throttled to 5 submissions per minute per IP on top of the honeypot check in
// StoreLeadRequest. 'throttle' is a framework alias, no named limiter needed.
Route::post('demande-demo', [LandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('demo.store');

Route::get('merci', [LandingController::class, 'thanks'])->name('thanks');
