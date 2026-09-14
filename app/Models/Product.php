<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'description',
        'brand',
        'measure_unity',
        'unit_price',
        'percentage_distributor',
        'price_distributor',
        'percentage_major',
        'price_major',
        'percentage_general',
        'price_general',
        'stock',
    ];

    /**
     * Transforma automáticamente los atributos a un formato decimal
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'percentage_distributor' => 'decimal:2',
            'price_distributor' => 'decimal:2',
            'percentage_major' => 'decimal:2',
            'price_major' => 'decimal:2',
            'percentage_general' => 'decimal:2',
            'price_general' => 'decimal:2',
            'stock' => 'decimal:2',
        ];
    }

    /**
     * Un producto aparece en muchas líneas de factura (1:N).
     */
    public function billerItems(): HasMany
    {
        return $this->hasMany(BillerItem::class);
    }

    /**
     * Un producto tiene muchos movimientos de stock (1:N).
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Relación muchos a muchos "lógica" con Biller a través de biller_items,
     * que además guarda datos propios de la línea (quantity, unit_price, subtotal).
     * Útil cuando quieras, por ejemplo, $product->billers para saber en qué
     * facturas se vendió, sin pasar explícitamente por BillerItem.
     */
    public function billers(): BelongsToMany
    {
        return $this->belongsToMany(Biller::class, 'biller_items')
            ->withPivot(['quantity', 'unit_price', 'subtotal'])
            ->using(BillerItem::class)
            ->withTimestamps();
    }
}
