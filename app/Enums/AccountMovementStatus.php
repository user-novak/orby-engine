<?php

namespace App\Enums;

/**
 * Valores posibles de account_movements.status.
 * CuentaPorCobrar / CuentaCobrada son el ciclo de vida del "pago pendiente"
 * que ya conoces: nace en CuentaPorCobrar y AccountMovementService lo pasa
 * a CuentaCobrada automáticamente cuando el saldo llega a 0.
 */
enum AccountMovementStatus: string
{
    case CuentaCobrada = 'cuenta_cobrada';
    case CuentaPagada = 'cuenta_pagada';
    case CuentaPorCobrar = 'cuenta_por_cobrar';
    case CuentaPorPagar = 'cuenta_por_pagar';
    case Amortizacion = 'amortizacion';

    public function label(): string
    {
        return match ($this) {
            self::CuentaCobrada => 'Cuenta cobrada',
            self::CuentaPagada => 'Cuenta pagada',
            self::CuentaPorCobrar => 'Cuenta por cobrar',
            self::CuentaPorPagar => 'Cuenta por pagar',
            self::Amortizacion => 'Amortización',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}