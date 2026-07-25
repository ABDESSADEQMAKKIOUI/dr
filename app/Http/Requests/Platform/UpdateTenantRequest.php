<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates edits to a tenant's descriptive fields. The slug and database_name
 * are immutable (the schema is already named after them) and are therefore not
 * accepted here. Plan changes flow through the subscription screens, not this
 * form. Authorisation is enforced by 'platform.ability:tenants.update'.
 */
class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'locale' => ['required', 'string', 'max:5'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:64', 'timezone'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
