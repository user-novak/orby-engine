<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'dni' => ['nullable', 'string', 'max:8', 'required_without:ruc', 'unique:clients,dni'],
            'name' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:11', 'required_without:dni', 'unique:clients,ruc'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
