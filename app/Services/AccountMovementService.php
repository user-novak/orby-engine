<?php

namespace App\Services;

use App\Enums\AccountMovementStatus;
use App\Enums\AccountMovementType;
use App\Models\AccountMovement;
use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountMovementService
{
    public function registerManual(User $user, array $data): AccountMovement
    {
        $this->assertCategoryIsAvailable($user, $data['category_id']);

        if (isset($data['account_id'])) {
            $this->assertAccountBelongsToUser($user, $data['account_id']);
        }

        return DB::transaction(fn () => AccountMovement::create([
            'amount' => $data['amount'],
            'movement_date' => $data['movement_date'],
            'type' => $data['type'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'account_id' => $data['account_id'] ?? null,
            'biller_id' => null,
            'user_id' => $user->id,
        ]));
    }

    public function registerPayment(
        User $user,
        AccountMovement $accountMovement,
        array $data,
    ): AccountMovement
    {
        $this->assertAccountBelongsToUser($user, $data['account_id']);

        return DB::transaction(function () use ($user, $accountMovement, $data) {
            $pending = AccountMovement::whereKey($accountMovement->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $pending->type !== AccountMovementType::Egreso->value
                || $pending->status !== AccountMovementStatus::CuentaPorPagar->value
            ) {
                throw ValidationException::withMessages([
                    'account_movement' => 'El movimiento no corresponde a una cuenta por pagar pendiente.',
                ]);
            }

            if ((float) $data['amount'] > (float) $pending->amount) {
                throw ValidationException::withMessages([
                    'amount' => "El monto no puede superar la deuda pendiente ({$pending->amount}).",
                ]);
            }

            $payment = AccountMovement::create([
                'amount' => $data['amount'],
                'movement_date' => now(),
                'type' => AccountMovementType::Egreso,
                'status' => AccountMovementStatus::Amortizacion,
                'description' => $data['description'] ?? 'Pago de cuenta por pagar',
                'category_id' => $pending->category_id,
                'account_id' => $data['account_id'],
                'biller_id' => $pending->biller_id,
                'user_id' => $user->id,
            ]);

            $remaining = round((float) $pending->amount - (float) $data['amount'], 2);

            $pending->update([
                'amount' => $remaining,
                'status' => $remaining <= 0
                    ? AccountMovementStatus::CuentaPagada
                    : AccountMovementStatus::CuentaPorPagar,
            ]);

            return $payment;
        });
    }

    public function registerCollection(
        User $user,
        AccountMovement $accountMovement,
        array $data,
    ): AccountMovement
    {
        $this->assertAccountBelongsToUser($user, $data['account_id']);

        return DB::transaction(function () use ($user, $accountMovement, $data) {
            $pending = AccountMovement::whereKey($accountMovement->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $pending->type !== AccountMovementType::Ingreso->value
                || $pending->status !== AccountMovementStatus::CuentaPorCobrar->value
            ) {
                throw ValidationException::withMessages([
                    'account_movement' => 'El movimiento no corresponde a una cuenta por cobrar pendiente.',
                ]);
            }

            if ((float) $data['amount'] > (float) $pending->amount) {
                throw ValidationException::withMessages([
                    'amount' => "El monto no puede superar la deuda pendiente ({$pending->amount}).",
                ]);
            }

            $collection = AccountMovement::create([
                'amount' => $data['amount'],
                'movement_date' => now(),
                'type' => AccountMovementType::Ingreso,
                'status' => AccountMovementStatus::Amortizacion,
                'description' => $data['description'] ?? 'Cobro de cuenta por cobrar',
                'category_id' => $pending->category_id,
                'account_id' => $data['account_id'],
                'biller_id' => $pending->biller_id,
                'user_id' => $user->id,
            ]);

            $remaining = round((float) $pending->amount - (float) $data['amount'], 2);

            $pending->update([
                'amount' => $remaining,
                'status' => $remaining <= 0
                    ? AccountMovementStatus::CuentaCobrada
                    : AccountMovementStatus::CuentaPorCobrar,
            ]);

            if ($remaining <= 0 && $pending->biller_id !== null) {
                Biller::query()
                    ->whereKey($pending->biller_id)
                    ->where('user_id', $user->id)
                    ->update(['paid_at' => now()]);
            }

            return $collection;
        });
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
    public function registerAmortization(User $user, Biller $biller, array $data): AccountMovement
    {
        $this->assertAccountBelongsToUser($user, $data['account_id']);

        return DB::transaction(function () use ($user, $biller, $data) {
            $pending = AccountMovement::where('biller_id', $biller->id)
                ->where('user_id', $user->id)
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
                'user_id' => $user->id,
            ]);

            $remaining = round($pending->amount - $data['amount'], 2);

            $pending->update([
                'amount' => $remaining,
                'status' => $remaining <= 0 ? AccountMovementStatus::CuentaCobrada : AccountMovementStatus::CuentaPorCobrar,
            ]);

            if ($remaining <= 0) {
                $biller->update(['paid_at' => now()]);
            }

            return $amortization;
        });
    }

    private function assertAccountBelongsToUser(User $user, int $accountId): void
    {
        $exists = Account::query()
            ->whereKey($accountId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'account_id' => 'La cuenta no pertenece al usuario autenticado.',
            ]);
        }
    }

    private function assertCategoryIsAvailable(User $user, int $categoryId): void
    {
        $exists = Category::query()
            ->whereKey($categoryId)
            ->where(function ($query) use ($user) {
                $query
                    ->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'category_id' => 'La categoría no está disponible para el usuario autenticado.',
            ]);
        }
    }
}
