<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: reemplazar por una policy/gate real cuando agregues auth.
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => [
                'nullable',
                'string',
                'max:8',
                Rule::unique('clients', 'dni')->where('user_id', $this->user()->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'ruc' => [
                'nullable',
                'string',
                'max:11',
                Rule::unique('clients', 'ruc')->where('user_id', $this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
