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
            'client_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('clients', 'id')->where('user_id', $this->user()->id),
            ],
            'account_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id),
            ],
            'product_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where('user_id', $this->user()->id),
            ],
            'sale_type' => ['sometimes', 'nullable', Rule::enum(SaleType::class)],
        ];
    }
}
