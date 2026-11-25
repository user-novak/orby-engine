<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: reemplazar por una policy/gate real cuando agregues auth.
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('user_id', $this->user()->id),
            ],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            // Opcional: si no la mandan, el Service usa "ahora".
            'movement_date' => ['nullable', 'date'],

            // 'type' y 'biller_id' NO tienen regla aquí a propósito.
            // Este endpoint es solo para entradas manuales (compras/ajustes):
            // StockMovementService fuerza siempre type = 'entrada' y
            // biller_id = null, sin importar qué mande el cliente en el body.
            // Las salidas ('salida') solo las genera BillerService al
            // procesar una venta.
        ];
    }
}
