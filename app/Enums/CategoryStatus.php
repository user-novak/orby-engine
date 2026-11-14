<?php

namespace App\Enums;

enum CategoryStatus: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
