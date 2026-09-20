<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_date' => $this->sale_date,
            'payment_date' => $this->payment_date,
            'sale_type' => $this->sale_type,
            'subtotal' => $this->subtotal,
            'igv' => $this->igv,
            'total' => $this->total,
            'client_id' => $this->client_id,
            'account_id' => $this->account_id,
            'client' => ClientResource::make($this->whenLoaded('client')),
            'items' => BillerItemResource::collection($this->whenLoaded('items')),
            'account_movements' => AccountMovementResource::collection($this->whenLoaded('accountMovements')),
            'created_at' => $this->created_at,
        ];
    }
}
