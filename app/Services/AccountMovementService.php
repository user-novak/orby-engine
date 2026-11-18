<?php

namespace App\Services;

use App\Enums\AccountMovementStatus;
use App\Enums\AccountMovementType;
use App\Models\AccountMovement;
use App\Models\Biller;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class AccountMovementService
{
    public function registerManual(array $data): AccountMovement
    {
        return DB::transaction(fn () => AccountMovement::create([
            'amount' => $data['amount'],
            'movement_date' => $data['movement_date'],
            'type' => $data['type'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'account_id' => $data['account_id'] ?? null,
            'biller_id' => null,
        ]));
    }

    /**
     * Registra una nueva amortización sobre la deuda pendiente de una
     * factura al crédito, y descuenta el monto del registro
     * 'cuenta_por_cobrar' correspondiente. Si la deuda queda saldada
     * (<= 0), el registro pendiente pasa a 'cuenta_cobrada'.
     *
     * Todo dentro de una transacción con lockForUpdate: si llegaran dos
     * amortizaciones casi al mismo tiempo para la misma factura, la segunda
     * espera a que la primera termine de actualizar el saldo pendiente,
     * evitando que ambas lean el mismo monto "viejo" y se pisen entre sí.
     *
     * La validación de "el monto no puede superar la deuda" ya se hizo en
     * StoreAmortizationRequest, así que aquí asumimos que $data es válido.
     */
    public function registerAmortization(Biller $biller, array $data): AccountMovement
    {
        return DB::transaction(function () use ($biller, $data) {
            $pending = AccountMovement::where('biller_id', $biller->id)
                ->where('status', AccountMovementStatus::CuentaPorCobrar)
                ->lockForUpdate()
                ->firstOrFail();

            $amortization = AccountMovement::create([
                'amount' => $data['amount'],
                'movement_date' => now(),
                'type' => AccountMovementType::Ingreso,
                'status' => AccountMovementStatus::Amortizacion,
                'description' => $data['description'] ?? null,
                'category_id' => Category::salesId(),
                'account_id' => $data['account_id'],
                'biller_id' => $biller->id,
            ]);

            $remaining = round($pending->amount - $data['amount'], 2);

            $pending->update([
                'amount' => $remaining,
                'status' => $remaining <= 0 ? AccountMovementStatus::CuentaCobrada : AccountMovementStatus::CuentaPorCobrar,
            ]);

            return $amortization;
        });
    }
}
