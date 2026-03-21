<?php

namespace App\Http\Requests\Biller;

use Illuminate\Foundation\Http\FormRequest;

class BillerRequest extends FormRequest
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
            'sale_date' => ['required', 'date'],
            'place' => ['nullable', 'string', 'max:255'],
            'sale_type' => ['required', 'in:ACO,ACR'],
            'client_id' => ['required', 'exists:clients,id'],
            'account_id' => ['required', 'exists:accounts,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.storage_id' => ['required', 'exists:storages,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
