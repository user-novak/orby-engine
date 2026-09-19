<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'account_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('accounts', 'account_number')->ignore($this->account),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            // 'amount' NO tiene regla aquí a propósito: el saldo se ajusta únicamente
            // creando registros en account_movements, nunca por edición directa de la cuenta.
        ];
    }
}
