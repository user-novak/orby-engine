<?php

namespace App\Observers;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementObserver
{
    /**
     * Cada vez que se registra un movimiento (entrada o salida),
     * ajustamos el contador cacheado products.stock dentro de una
     * transacción con lockForUpdate para evitar condiciones de carrera
     * si hay ventas simultáneas del mismo producto.
     */
    public function created(StockMovement $stockMovement): void
    {
        $type = $stockMovement->type instanceof StockMovementType
            ? $stockMovement->type
            : StockMovementType::from($stockMovement->type);

        $sign = match ($type) {
            StockMovementType::Entrada => 1,
            StockMovementType::Salida => -1,
        };

        $this->applyDelta($stockMovement, $sign);
    }

    private function applyDelta(StockMovement $stockMovement, int $sign): void
    {
        DB::transaction(function () use ($stockMovement, $sign) {
            $product = Product::whereKey($stockMovement->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $product->increment('stock', $sign * $stockMovement->quantity);
        });
    }
}
