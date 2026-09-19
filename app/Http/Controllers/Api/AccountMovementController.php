<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountMovementResource;
use App\Models\AccountMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * También de solo lectura. La creación de un AccountMovement siempre pasa
 * por una acción de negocio específica:
 * - La venta (al contado o al crédito) crea el/los movimientos iniciales
 *   dentro de BillerService (pendiente de construir).
 * - Una amortización posterior sobre una deuda existente pasa por
 *   AmortizationController, que sí valida reglas propias (no amortizar más
 *   de lo que se debe, actualizar el saldo pendiente).
 * Un store() genérico aquí permitiría crear movimientos inconsistentes con
 * el resto del ledger, así que no lo exponemos.
 */
class AccountMovementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $movements = AccountMovement::query()
            ->with('account')
            ->when(
                $request->filled('account_id'),
                fn ($query) => $query->where('account_id', $request->integer('account_id'))
            )
            ->when(
                $request->filled('biller_id'),
                fn ($query) => $query->where('biller_id', $request->integer('biller_id'))
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status'))
            )
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', $request->string('type'))
            )
            ->latest('movement_date')
            ->paginate();

        return AccountMovementResource::collection($movements);
    }

    public function show(AccountMovement $accountMovement): AccountMovementResource
    {
        return AccountMovementResource::make($accountMovement->load('account'));
    }
}
