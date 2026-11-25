<?php

namespace App\Http\Requests;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->category->user_id !== null
            && $this->category->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(function (Builder $query) {
                        $query->where(function (Builder $owner) {
                            $owner
                                ->whereNull('user_id')
                                ->orWhere('user_id', $this->user()->id);
                        });
                    })
                    ->ignore($this->category),
            ],
            'type' => ['sometimes', 'required', Rule::enum(CategoryType::class)],
            'status' => ['sometimes', 'required', Rule::enum(CategoryStatus::class)],
        ];
    }
}
