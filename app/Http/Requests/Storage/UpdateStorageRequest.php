<?php

namespace App\Http\Requests\Storage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorageRequest extends FormRequest
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
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'brand' => ['sometimes', 'required', 'string', 'max:255'],
            'measure_unity' => ['sometimes', 'required', 'string', 'max:100'],
            'unit_price' => ['sometimes', 'required', 'numeric'],

            'percentage_distributor' => ['sometimes', 'required', 'numeric'],
            'price_distributor' => ['sometimes', 'required', 'numeric'],

            'percentage_major' => ['sometimes', 'required', 'numeric'],
            'price_major' => ['sometimes', 'required', 'numeric'],

            'percentage_general' => ['sometimes', 'required', 'numeric'],
            'price_general' => ['sometimes', 'required', 'numeric'],

            'input' => ['sometimes', 'required', 'integer'],
            'output' => ['sometimes', 'required', 'integer'],
            'stock' => ['sometimes', 'required', 'integer'],
        ];
    }
}
