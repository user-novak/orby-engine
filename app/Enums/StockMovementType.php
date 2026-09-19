<?php

namespace App\Enums;

/**
 * Valores posibles de stock_movements.type.
 */
enum StockMovementType: string
{
    case Entrada = 'entrada';
    case Salida = 'salida';

    public function label(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Salida => 'Salida',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}