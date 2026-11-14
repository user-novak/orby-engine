<?php

namespace App\Enums;

enum CategoryType: string
{
    case Ingreso = 'ingreso';
    case Egreso = 'egreso';
    case Ambos = 'ambos';

    public function label(): string
    {
        return match ($this) {
            self::Ingreso => 'Ingreso',
            self::Egreso => 'Egreso',
            self::Ambos => 'Ambos',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
