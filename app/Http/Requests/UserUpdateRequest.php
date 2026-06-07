<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
            'warehouse_ids' => ['nullable', 'array'],
            'warehouse_ids.*' => ['exists:warehouses,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'role_ids.required' => 'Au moins un rôle doit être sélectionné.',
        ];
    }
}
