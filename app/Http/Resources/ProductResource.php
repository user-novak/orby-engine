<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'brand' => $this->brand,
            'measure_unity' => $this->measure_unity,
            'unit_price' => $this->unit_price,
            'percentage_distributor' => $this->percentage_distributor,
            'price_distributor' => $this->price_distributor,
            'percentage_major' => $this->percentage_major,
            'price_major' => $this->price_major,
            'percentage_general' => $this->percentage_general,
            'price_general' => $this->price_general,
            'stock' => $this->stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
