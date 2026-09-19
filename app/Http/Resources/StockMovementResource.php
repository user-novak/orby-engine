<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'movement_date' => $this->movement_date,
            'product_id' => $this->product_id,
            'biller_id' => $this->biller_id,
            // Solo se incluye si el controller hizo ->load('product') o ->with('product').
            // Evita el problema N+1 de cargar la relación en cada fila "por si acaso".
            'product' => ProductResource::make($this->whenLoaded('product')),
            'created_at' => $this->created_at,
        ];
    }
}
