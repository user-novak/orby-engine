<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function (Builder $query) {
                    $query->where(function (Builder $owner) {
                        $owner
                            ->whereNull('user_id')
                            ->orWhere('user_id', $this->user()->id);
                    });
                }),
            ],
            'type' => ['required', Rule::enum(CategoryType::class)],
        ];
    }
}
