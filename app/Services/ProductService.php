<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function create(array $data): Product
    {
        $this->validatePrices(null, $data);

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $this->validatePrices($product, $data);
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function validatePrices(?Product $existing, array $data): void
    {
        $unitPrice = (float) ($data['unit_price'] ?? $existing?->unit_price ?? 0);

        $percentageDistributor = (float) ($data['percentage_distributor'] ?? $existing?->percentage_distributor ?? 0);
        $percentageMajor = (float) ($data['percentage_major'] ?? $existing?->percentage_major ?? 0);
        $percentageGeneral = (float) ($data['percentage_general'] ?? $existing?->percentage_general ?? 0);

        $prices = [
            'price_distributor' => [
                'value' => $data['price_distributor'] ?? $existing?->price_distributor,
                'expected' => $this->applyMargin($unitPrice, $percentageDistributor),
            ],
            'price_major' => [
                'value' => $data['price_major'] ?? $existing?->price_major,
                'expected' => $this->applyMargin($unitPrice, $percentageMajor),
            ],
            'price_general' => [
                'value' => $data['price_general'] ?? $existing?->price_general,
                'expected' => $this->applyMargin($unitPrice, $percentageGeneral),
            ],
        ];

        $errors = [];

        foreach ($prices as $field => $price) {
            if (round((float) $price['value'], 2) !== $price['expected']) {
                $errors[$field] = "El valor debe ser {$price['expected']}.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function applyMargin(float $unitPrice, float $percentage): float
    {
        return round($unitPrice + ($unitPrice * $percentage / 100), 2);
    }
}
