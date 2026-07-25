<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates edits to an existing operator. The password is optional (left blank
 * to keep the current one); the unique email rule ignores the operator being
 * edited. Authorisation is enforced by 'platform.ability:operators.manage'.
 */
class UpdateOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $operatorId = $this->route('operator')?->getKey();

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('platform.platform_users', 'email')->ignore($operatorId)->withoutTrashed(),
            ],
            'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
            'role' => ['required', Rule::in(['owner', 'admin', 'support', 'billing'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
