<?php

namespace App\Http\Requests\Storage;

use Illuminate\Foundation\Http\FormRequest;

class StoreStorageRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'measure_unity' => ['required', 'string', 'max:100'],
            'unit_price' => ['required', 'numeric'],

            'percentage_distributor' => ['required', 'numeric'],
            'price_distributor' => ['required', 'numeric'],

            'percentage_major' => ['required', 'numeric'],
            'price_major' => ['required', 'numeric'],

            'percentage_general' => ['required', 'numeric'],
            'price_general' => ['required', 'numeric'],

            'input' => ['required', 'integer'],
            'output' => ['required', 'integer'],
            'stock' => ['required', 'integer'],
        ];
    }
}
