<?php

namespace App\Http\Requests;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->category),
            ],
            'type' => ['sometimes', 'required', Rule::enum(CategoryType::class)],
            'status' => ['sometimes', 'required', Rule::enum(CategoryStatus::class)],
        ];
    }
}
