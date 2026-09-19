<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'measure_unity' => ['sometimes', 'required', 'string', 'max:20'],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'percentage_distributor' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'percentage_major' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'percentage_general' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            // 'stock' no tiene regla: se protege igual que 'amount' en accounts.
            // price_distributor/price_major/price_general tampoco: se recalculan
            // automáticamente si cambia unit_price o algún percentage_*.
        ];
    }
}
