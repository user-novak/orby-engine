<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'movement_date' => $this->movement_date,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'account_id' => $this->account_id,
            'biller_id' => $this->biller_id,
            'account' => AccountResource::make($this->whenLoaded('account')),
        ];
    }
}
