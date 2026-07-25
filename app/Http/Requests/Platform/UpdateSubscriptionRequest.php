<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the single subscription mutation endpoint. Every subscription action
 * funnels through PUT /subscriptions/{subscription}; the `action` field selects
 * which SubscriptionManager operation the controller performs:
 *
 *   change_plan  -> requires plan_id  (switch to a different plan)
 *   renew        -> extend the paid period by one billing cycle
 *   cancel       -> mark cancelled (optional reason)
 *   update       -> edit the non-invariant fields (grace_days, notes)
 *
 * Authorisation is enforced by 'platform.ability:subscriptions.update'.
 */
class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['change_plan', 'renew', 'cancel', 'update'])],
            'plan_id' => [
                'required_if:action,change_plan',
                'integer',
                Rule::exists('platform.plans', 'id')->where('is_active', true),
            ],
            'reason' => ['nullable', 'string', 'max:255'],
            'grace_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
