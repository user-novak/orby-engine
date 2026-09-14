<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Biller extends Model
{
    protected $fillable = [
        'sale_date',
        'payment_date',
        'sale_type',
        'subtotal',
        'igv',
        'total',
        'client_id',
        'account_id',
    ];

    /**
     * Transforma automáticamente los atributos a un formato decimal y fecha
     */
    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'payment_date' => 'date',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Una factura pertenece a un cliente (N:1).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Una factura pertenece opcionalmente a una cuenta (N:1).
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Una factura tiene muchas líneas de venta (1:N).
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillerItem::class);
    }

    /**
     * Una factura genera muchos movimientos de stock (1:N).
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Una factura genera muchos movimientos de cuenta:
     * amortización(es) + pago pendiente (1:N).
     */
    public function accountMovements(): HasMany
    {
        return $this->hasMany(AccountMovement::class);
    }

    /**
     * Muchos a muchos "lógico" con Product a través de biller_items.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'biller_items')
            ->withPivot(['quantity', 'unit_price', 'subtotal'])
            ->using(BillerItem::class)
            ->withTimestamps();
    }
}
