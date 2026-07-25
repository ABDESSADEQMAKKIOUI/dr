<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreLeadRequest;
use App\Models\Platform\Lead;
use App\Models\Platform\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * The public face of SAFM, served on the apex host (facturation.cfpss.ma).
 *
 * These actions run OUTSIDE tenancy: ResolveTenant is not in the 'site'
 * middleware group, so config('database.default') still points at the sentinel
 * schema and no tenant database exists. Every model touched here therefore has
 * to be one of the App\Models\Platform\* models, which pin $connection to
 * 'platform' themselves.
 */
class LandingController extends Controller
{
    /** Locales the public site ships translations for. */
    private const LOCALES = ['fr', 'en'];

    /**
     * The landing page: product pitch, features, pricing and the demo form.
     *
     * Pricing is read from the plans table rather than hard-coded so the
     * operator console stays the single place a price is ever changed.
     */
    public function index(Request $request): View
    {
        $this->applyPublicLocale($request);

        return view('site.landing', [
            'plans' => $this->activePlans(),
            'honeypotField' => StoreLeadRequest::HONEYPOT_FIELD,
        ]);
    }

    /**
     * Capture a demo request as a platform lead.
     *
     * The request has already been validated and rate-limited by the time we
     * get here (StoreLeadRequest + throttle:5,1 on the route).
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        // Honeypot. Answer a bot exactly the way we answer a human — same
        // redirect, same flash message — so a submission gives away nothing
        // about whether it was accepted. Nothing is written.
        if (! $request->isHuman()) {
            return $this->redirectToThanks();
        }

        $data = $request->validated();

        Lead::create([
            'company_name' => $data['company_name'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,

            // Server-side ONLY. Never read from input: `status` drives the
            // console pipeline and `source` drives its reporting, so a visitor
            // able to set either could forge an already-qualified lead.
            'source' => Lead::SOURCE_DEMO,
            'status' => Lead::STATUS_NEW,

            // Kept for abuse triage in the console: two hundred leads from one
            // address is a pattern an operator needs to be able to see.
            // user_agent is a VARCHAR(255) and clients send far longer strings.
            'ip_address' => $request->ip(),
            'user_agent' => $this->userAgent($request),
        ]);

        return $this->redirectToThanks();
    }

    /**
     * Confirmation page reached after a successful submission. Also renders on
     * a direct visit — it is a static courtesy page, nothing is exposed.
     */
    public function thanks(Request $request): View
    {
        $this->applyPublicLocale($request);

        return view('site.thanks', [
            'message' => $request->session()->get('status') ?: __('app.lead_thanks_message'),
        ]);
    }

    /**
     * Active plans in display order.
     *
     * Wrapped because this is a marketing page: if safm_platform is unreachable
     * the pitch, the features and the demo form are all still worth serving, so
     * a database outage degrades the pricing section instead of returning a 500
     * to every prospect who lands on the site.
     *
     * @return Collection<int, Plan>
     */
    private function activePlans(): Collection
    {
        try {
            return Plan::query()->active()->ordered()->get();
        } catch (Throwable $e) {
            Log::error('Landing page could not load plans from the platform connection.', [
                'exception' => $e->getMessage(),
            ]);

            return new Collection();
        }
    }

    private function redirectToThanks(): RedirectResponse
    {
        return redirect()
            ->route('site.thanks')
            ->with('status', __('app.lead_thanks_message'));
    }

    private function userAgent(Request $request): ?string
    {
        $agent = $request->userAgent();

        if ($agent === null || $agent === '') {
            return null;
        }

        return mb_substr($agent, 0, 255);
    }

    /**
     * config('app.locale') is 'en' by default for the ERP. The public site is
     * French-first, with ?lang=en honoured because both lang/fr/app.php and
     * lang/en/app.php carry the site's keys.
     */
    private function applyPublicLocale(Request $request): void
    {
        $locale = mb_strtolower((string) $request->query('lang', 'fr'));

        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'fr');
    }
}
