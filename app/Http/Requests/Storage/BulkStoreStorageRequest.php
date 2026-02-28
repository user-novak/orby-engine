<?php

namespace App\Http\Requests\Storage;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreStorageRequest extends FormRequest
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
            'storages' => ['required', 'array'],
            'storages.*.description' => ['required', 'string'],
            'storages.*.brand' => ['required', 'string'],
            'storages.*.measure_unity' => ['required', 'string'],
            'storages.*.unit_price' => ['required', 'numeric'],

            'storages.*.percentage_distributor' => ['required', 'numeric'],
            'storages.*.price_distributor' => ['required', 'numeric'],

            'storages.*.percentage_major' => ['required', 'numeric'],
            'storages.*.price_major' => ['required', 'numeric'],

            'storages.*.percentage_general' => ['required', 'numeric'],
            'storages.*.price_general' => ['required', 'numeric'],

            'storages.*.input' => ['required', 'integer'],
            'storages.*.output' => ['required', 'integer'],
            'storages.*.stock' => ['required', 'integer'],
        ];
    }
}
