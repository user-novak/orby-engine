<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAmortizationRequest;
use App\Http\Resources\AccountMovementResource;
use App\Models\Biller;
use App\Services\AccountMovementService;

/**
 * "Single action controller": esta acción no es un recurso CRUD, es una
 * operación de negocio puntual (registrar una nueva amortización sobre
 * una factura al crédito con deuda pendiente), por eso no vive dentro de
 * AccountMovementController ni se llama "store" de nada. Convención
 * Laravel: __invoke() + registrar la ruta como Route::post(..., Controller).
 */
class AmortizationController extends Controller
{
    public function __construct(private readonly AccountMovementService $accountMovements)
    {
    }

    public function __invoke(StoreAmortizationRequest $request, Biller $biller): AccountMovementResource
    {
        $amortization = $this->accountMovements->registerAmortization($biller, $request->validated());

        return AccountMovementResource::make($amortization);
    }
}
