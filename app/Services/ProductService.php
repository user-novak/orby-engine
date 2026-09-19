<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function create(array $data): Product
    {
        return Product::create($this->withCalculatedPrices(null, $data));
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($this->withCalculatedPrices($product, $data));

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * price_distributor / price_major / price_general nunca los pone el usuario:
     * se derivan de unit_price + el percentage_* correspondiente. Si el update es
     * parcial (PATCH) y no manda unit_price o algún percentage_*, usamos el valor
     * que el producto ya tiene guardado para ese cálculo.
     */
    private function withCalculatedPrices(?Product $existing, array $data): array
    {
        $unitPrice = (float) ($data['unit_price'] ?? $existing?->unit_price ?? 0);

        $percentageDistributor = (float) ($data['percentage_distributor'] ?? $existing?->percentage_distributor ?? 0);
        $percentageMajor = (float) ($data['percentage_major'] ?? $existing?->percentage_major ?? 0);
        $percentageGeneral = (float) ($data['percentage_general'] ?? $existing?->percentage_general ?? 0);

        $data['price_distributor'] = $this->applyMargin($unitPrice, $percentageDistributor);
        $data['price_major'] = $this->applyMargin($unitPrice, $percentageMajor);
        $data['price_general'] = $this->applyMargin($unitPrice, $percentageGeneral);

        return $data;
    }

    private function applyMargin(float $unitPrice, float $percentage): float
    {
        return round($unitPrice + ($unitPrice * $percentage / 100), 2);
    }
}
