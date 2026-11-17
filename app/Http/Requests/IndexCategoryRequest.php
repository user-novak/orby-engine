<?php

namespace App\Http\Requests;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCategoryRequest extends FormRequest
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
            'type' => ['sometimes', Rule::enum(CategoryType::class)],
            'status' => ['sometimes', Rule::enum(CategoryStatus::class)],
        ];
    }
}
