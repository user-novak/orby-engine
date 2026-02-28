<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreAccountRequest extends FormRequest
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
            'accounts' => ['required', 'array'],
            'accounts.*.name' => ['required', 'string', 'max:255'],
            'accounts.*.amount' => ['required', 'numeric'],
            'accounts.*.account_number' => ['nullable', 'string', 'max:255'],
            'accounts.*.description' => ['nullable', 'string'],
        ];
    }
}
