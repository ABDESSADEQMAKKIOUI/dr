<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates the operator signup form that drives TenantProvisioner::provision().
 * Its rules mirror the provisioner's $data contract exactly, so a passing
 * request maps straight onto the provisioning payload.
 *
 * Authorisation is handled upstream by the 'platform.ability:tenants.create'
 * middleware on the route, hence authorize() returns true.
 */
class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => strtolower(trim((string) $this->input('slug')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:63',
                'regex:'.config('tenancy.slug_pattern'),
                Rule::notIn(config('tenancy.reserved_slugs', [])),
                Rule::unique('platform.tenants', 'slug')->withoutTrashed(),
            ],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'locale' => ['required', 'string', 'max:5'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:64', 'timezone'],
            'notes' => ['nullable', 'string', 'max:65535'],

            'plan_id' => [
                'required',
                'integer',
                Rule::exists('platform.plans', 'id')->where('is_active', true),
            ],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],

            'admin_first_name' => ['required', 'string', 'max:100'],
            'admin_last_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'max:190'],
            'admin_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'admin_phone' => ['nullable', 'string', 'max:30'],

            'warehouse_name' => ['nullable', 'string', 'max:150'],
            'warehouse_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => __('tenancy.slug_invalid'),
            'slug.not_in' => __('tenancy.slug_reserved'),
            'slug.unique' => __('tenancy.slug_taken'),
        ];
    }
}
