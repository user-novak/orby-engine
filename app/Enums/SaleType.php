<?php

namespace App\Enums;

/**
 * Valores posibles de billers.sale_type.
 * ->value debe coincidir EXACTO con el string ya guardado en la columna
 * enum() de la migración de billers. Si algún día agregas un caso nuevo,
 * también hay que agregarlo al enum() de una migración nueva (alter table).
 */
enum SaleType: string
{
    case Contado = 'contado';
    case Credito = 'credito';

    public function label(): string
    {
        return match ($this) {
            self::Contado => 'Al contado',
            self::Credito => 'Al crédito',
        };
    }

    /** Útil para Blueprint::enum() en migraciones o para poblar un <select>. */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}