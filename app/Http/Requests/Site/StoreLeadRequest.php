<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the public demo / contact form on the apex landing page.
 *
 * Two things make this request different from every other FormRequest in the
 * app and both are deliberate:
 *
 *   1. It is UNAUTHENTICATED. authorize() returns true because there is nobody
 *      to authorise — the route is open to the internet. Abuse is handled by
 *      the throttle on the route plus the honeypot below, not by a guard.
 *
 *   2. It NEVER accepts `source` or `status`. Those two columns drive the
 *      operator console's pipeline; letting a visitor post status=converted
 *      would let anyone forge a qualified lead. LandingController sets both
 *      server-side from the model's own constants.
 */
class StoreLeadRequest extends FormRequest
{
    /**
     * Name of the honeypot input the landing page must render (hidden, empty,
     * autocomplete off). Public so resources/views/site/landing.blade.php can
     * reference it instead of hard-coding the string in two places.
     *
     * "website" is chosen because it is plausible enough that a form-filling
     * bot will populate it, which is exactly the signal we want.
     */
    public const HONEYPOT_FIELD = 'website';

    /** Locales the public site ships translations for. */
    private const LOCALES = ['fr', 'en'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Whether the submission looks like it came from a real browser.
     *
     * A human never sees the honeypot input and therefore never fills it. The
     * controller checks this BEFORE persisting and pretends the submission
     * succeeded when it fails, so a bot gets no feedback to tune against.
     *
     * Anything that is not a genuinely empty string — including an array, which
     * is how a scattergun bot posts `website[]=x` — counts as filled.
     */
    public function isHuman(): bool
    {
        $value = $this->input(self::HONEYPOT_FIELD);

        if ($value === null) {
            return true;
        }

        return is_string($value) && trim($value) === '';
    }

    protected function prepareForValidation(): void
    {
        // config('app.locale') is 'en' by default for the ERP; the public site
        // is French-first. Set it here as well as in the controller because
        // validation — and therefore the error messages rendered back onto the
        // landing page — runs before any controller action.
        $this->applyPublicLocale();

        $normalised = [];

        if (is_string($this->input('email'))) {
            $normalised['email'] = mb_strtolower(trim($this->input('email')));
        }

        // An unselected <select> posts ''. Laravel's global
        // ConvertEmptyStringsToNull normally handles this, but plan_id must be
        // null rather than '' for the nullable rule to short-circuit even if
        // that middleware is ever removed from the global stack.
        if ($this->input('plan_id') === '') {
            $normalised['plan_id'] = null;
        }

        if ($normalised !== []) {
            $this->merge($normalised);
        }
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'contact_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],

            // 'platform.plans' is connection.table — the plans table lives in
            // safm_platform and the apex has no tenant connection at all.
            // Restricted to active plans so a visitor cannot express interest
            // in an offer that was withdrawn.
            'plan_id' => [
                'nullable',
                'integer',
                Rule::exists('platform.plans', 'id')->where('is_active', true),
            ],

            // NOTE: self::HONEYPOT_FIELD is deliberately NOT declared here.
            // Giving it a rule would surface a validation error next to a field
            // the visitor cannot see, and would tell a bot the trap exists. It
            // is read raw by isHuman() and never reaches validated().
        ];
    }

    /**
     * Attribute names for the French validation messages. Every key already
     * exists in lang/fr/app.php and lang/en/app.php.
     */
    public function attributes(): array
    {
        return [
            'company_name' => __('app.company_name'),
            'contact_name' => __('app.contact_name'),
            'email' => __('app.email'),
            'phone' => __('app.phone'),
            'message' => __('app.message'),
            'plan_id' => __('app.plan'),
        ];
    }

    private function applyPublicLocale(): void
    {
        $locale = mb_strtolower((string) $this->query('lang', 'fr'));

        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'fr');
    }
}
