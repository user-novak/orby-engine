<?php

namespace App\Services;

use App\Models\StockMovement;

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
    public function registerManualEntry(array $data): StockMovement
    {
        return StockMovement::create([
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'movement_date' => $data['movement_date'] ?? now(),
            'type' => 'entrada',
            'biller_id' => null,
        ]);
    }
}
