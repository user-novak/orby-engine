<?php

namespace App\Models;

use App\Enums\PriceTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillerItem extends Model
{
    protected $fillable = [
        'biller_id',
        'product_id',
        'quantity',
        'unit_price',
        'sell_percentage',
        'type_percentage',
        'subtotal',
    ];

    /**
     * Transforma automáticamente los atributos a un formato decimal
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'sell_percentage' => 'decimal:2',
            'type_percentage' => PriceTier::class,
            'subtotal' => 'decimal:2',
        ];
    }

    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
