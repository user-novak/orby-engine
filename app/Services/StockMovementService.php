<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    /**
     * Registra una entrada manual de stock (compra, ajuste de inventario, etc).
     *
     * Forzamos type = 'entrada' y biller_id = null aquí, no en el Request,
     * para que quede blindado incluso si en el futuro alguien reutiliza este
     * método desde otro lugar del código con un array de datos "confiable".
     * El ajuste real de products.stock lo hace StockMovementObserver al
     * detectar el evento 'created' de este modelo — este Service no lo toca.
     */
    public function registerManualEntry(User $user, array $data): StockMovement
    {
        $productExists = Product::query()
            ->whereKey($data['product_id'])
            ->where('user_id', $user->id)
            ->exists();

        if (! $productExists) {
            throw ValidationException::withMessages([
                'product_id' => 'El producto no pertenece al usuario autenticado.',
            ]);
        }

        return StockMovement::create([
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'movement_date' => $data['movement_date'] ?? now(),
            'type' => StockMovementType::Entrada,
            'biller_id' => null,
            'user_id' => $user->id,
        ]);
    }
}
