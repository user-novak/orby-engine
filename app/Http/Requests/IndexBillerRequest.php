<?php

namespace App\Http\Requests;

use App\Enums\SaleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBillerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => [
                'sometimes',
                'nullable',
                'date',
                Rule::when($this->filled('date_from'), ['after_or_equal:date_from']),
            ],
            'client_id' => ['sometimes', 'nullable', 'integer', 'exists:clients,id'],
            'account_id' => ['sometimes', 'nullable', 'integer', 'exists:accounts,id'],
            'product_id' => ['sometimes', 'nullable', 'integer', 'exists:products,id'],
            'sale_type' => ['sometimes', 'nullable', Rule::enum(SaleType::class)],
        ];
    }
}
