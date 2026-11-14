<?php

namespace App\Enums;

use App\Models\Product;

/**
 * Nivel de precio con el que se vende un producto en una línea de factura.
 * Se usa para resolver las columnas price_* y percentage_* de products; el
 * resultado queda guardado en biller_items para conservar la trazabilidad.
 *
 * Cada caso mapea 1:1 a una columna price_* ya calculada por ProductService
 * (unit_price + su percentage_* correspondiente). Si el día de mañana
 * agregas un tier nuevo, este es el único lugar que hay que tocar para que
 * BillerService lo entienda.
 */
enum PriceTier: string
{
    case Distributor = 'distributor';
    case Major = 'major';
    case General = 'general';

    public function priceColumn(): string
    {
        return match ($this) {
            self::Distributor => 'price_distributor',
            self::Major => 'price_major',
            self::General => 'price_general',
        };
    }

    public function percentageColumn(): string
    {
        return match ($this) {
            self::Distributor => 'percentage_distributor',
            self::Major => 'percentage_major',
            self::General => 'percentage_general',
        };
    }

    public function resolvePrice(Product $product): float
    {
        return (float) $product->{$this->priceColumn()};
    }

    public function resolvePercentage(Product $product): float
    {
        return (float) $product->{$this->percentageColumn()};
    }

    public function label(): string
    {
        return match ($this) {
            self::Distributor => 'Distribuidor',
            self::Major => 'Mayorista',
            self::General => 'General',
        };
    }
}
