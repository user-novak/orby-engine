<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'measure_unity' => ['required', 'string', 'max:20'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'percentage_distributor' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'percentage_major' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'percentage_general' => ['nullable', 'numeric', 'min:0', 'max:100'],
            // Stock inicial. A partir de aquí, solo cambia vía stock_movements.
            'stock' => ['nullable', 'numeric', 'min:0'],
            // price_distributor / price_major / price_general NO se validan aquí:
            // se calculan siempre en ProductService, nunca vienen del cliente.
        ];
    }
}
