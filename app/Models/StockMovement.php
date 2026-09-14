<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'type',
        'quantity',
        'movement_date',
        'product_id',
        'biller_id',
    ];

    /**
     * Transforma automáticamente los atributos a un formato decimal y fecha
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'movement_date' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Nullable: hay movimientos de stock que no vienen de una venta
     * (por ejemplo, un ingreso manual de mercadería).
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }
}
