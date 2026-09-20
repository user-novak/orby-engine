<?php

namespace App\Http\Requests;

use App\Enums\PriceTier;
use App\Enums\SaleType;
use App\Models\Product;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBillerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: reemplazar por una policy/gate real cuando agregues auth.
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'sale_type' => ['required', Rule::enum(SaleType::class)],
            'sale_date' => ['required', 'date'],
            'payment_date' => ['nullable', 'date', 'after_or_equal:sale_date'],

            // Obligatoria en 'contado' (ahí se paga todo de una); en
            // 'credito' solo si además mandan un pago inicial.
            'account_id' => [
                'nullable',
                'integer',
                'exists:accounts,id',
                Rule::requiredIf(
                    fn () => $this->input('sale_type') === SaleType::Contado->value
                        || $this->filled('initial_payment')
                ),
            ],

            // Solo tiene sentido en una venta al crédito (pago parcial al
            // momento de vender). En 'contado' no existe: todo se cobra ya.
            'initial_payment' => [
                'nullable',
                'numeric',
                'min:0.01',
                Rule::prohibitedIf(fn () => $this->input('sale_type') === SaleType::Contado->value),
            ],

            'items' => ['required', 'array', 'min:1'],
            // 'distinct': no permitimos el mismo producto dos veces en una
            // misma venta (si se necesita más cantidad, se suma en un solo
            // item). Avísame si prefieres permitir líneas repetidas.
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price_tier' => ['required', Rule::enum(PriceTier::class)],

            // subtotal / igv / total / unit_price NO se validan aquí:
            // BillerService los calcula siempre, nunca vienen del cliente.
        ];
    }

    /**
     * Chequeo de stock "amigable": si algo no alcanza, el cliente de la API
     * recibe un 422 con el campo exacto y un mensaje claro. Esto es una
     * validación de "input", no lógica de negocio, por eso vive en el
     * Request. BillerService VUELVE a chequear esto mismo justo antes de
     * descontar el stock (con lockForUpdate, dentro de la transacción) —
     * esa segunda validación es la que realmente evita una condición de
     * carrera si dos ventas del mismo producto llegan casi al mismo tiempo.
     * Esta de aquí es solo para dar un buen mensaje de error sin llegar a
     * abrir una transacción.
     */
    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator) {
            $items = $this->input('items');

            if (! is_array($items)) {
                return;
            }

            $productIds = array_column($items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $index => $item) {
                $product = $products[$item['product_id'] ?? null] ?? null;

                if ($product && (float) ($item['quantity'] ?? 0) > (float) $product->stock) {
                    $validator->errors()->add(
                        "items.$index.quantity",
                        "Stock insuficiente para \"{$product->description}\" (disponible: {$product->stock})."
                    );
                }
            }
        });
    }
}
