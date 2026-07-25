<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates creation of a platform operator. The email uniqueness check runs on
 * the platform connection. Authorisation is enforced by
 * 'platform.ability:operators.manage'.
 */
class StoreOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('platform.platform_users', 'email')->withoutTrashed(),
            ],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
            'role' => ['required', Rule::in(['owner', 'admin', 'support', 'billing'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
