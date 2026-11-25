<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('accounts', 'account_number')
                    ->where('user_id', $this->user()->id),
            ],
            // Saldo inicial. A partir de aquí, el saldo solo cambia vía account_movements.
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
