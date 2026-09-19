<?php

namespace App\Enums;

/**
 * Valores posibles de account_movements.type.
 * No confundir con AccountMovementStatus: 'type' es la dirección del
 * dinero (entra/sale de la cuenta), 'status' es el motivo del movimiento
 * dentro del flujo de facturación (amortización, cuenta por cobrar, etc).
 */
enum AccountMovementType: string
{
    case Ingreso = 'ingreso';
    case Egreso = 'egreso';

    public function label(): string
    {
        return match ($this) {
            self::Ingreso => 'Ingreso',
            self::Egreso => 'Egreso',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}