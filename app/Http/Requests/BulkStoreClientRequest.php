<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clients' => ['required', 'array', 'min:1'],
            'clients.*.dni' => ['nullable', 'string', 'distinct'],
            'clients.*.name' => ['required', 'string'],
            'clients.*.ruc' => ['nullable', 'string'],
            'clients.*.phone' => ['nullable', 'string'],
            'clients.*.address' => ['nullable', 'string'],
        ];
    }
}
