<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountMovementRequest;
use App\Http\Requests\StoreAccountCollectionRequest;
use App\Http\Requests\StoreAccountPaymentRequest;
use App\Http\Resources\AccountMovementResource;
use App\Models\AccountMovement;
use App\Services\AccountMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Los movimientos manuales se pueden registrar mediante store(). Los demás
 * movimientos siguen pasando por acciones de negocio específicas:
 * - La venta (al contado o al crédito) crea el/los movimientos iniciales
 *   dentro de BillerService (pendiente de construir).
 * - Una amortización posterior sobre una deuda existente pasa por
 *   AmortizationController, que sí valida reglas propias (no amortizar más
 *   de lo que se debe, actualizar el saldo pendiente).
 */
class AccountMovementController extends Controller
{
    public function __construct(private readonly AccountMovementService $accountMovements)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $movements = AccountMovement::query()
            ->where('user_id', $request->user()->id)
            ->with(['account', 'category'])
            ->when(
                $request->filled('account_id'),
                fn ($query) => $query->where('account_id', $request->integer('account_id'))
            )
            ->when(
                $request->filled('biller_id'),
                fn ($query) => $query->where('biller_id', $request->integer('biller_id'))
            )
            ->when(
                $request->filled('category_id'),
                fn ($query) => $query->where('category_id', $request->integer('category_id'))
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
            ->paginate($request->integer('per_page', 15));

        return AccountMovementResource::collection($movements);
    }

    public function store(StoreAccountMovementRequest $request): AccountMovementResource
    {
        $movement = $this->accountMovements->registerManual(
            $request->user(),
            $request->validated(),
        );

        return AccountMovementResource::make($movement->load(['account', 'category']));
    }

    public function pay(
        StoreAccountPaymentRequest $request,
        AccountMovement $accountMovement,
    ): AccountMovementResource {
        $payment = $this->accountMovements->registerPayment(
            $request->user(),
            $accountMovement,
            $request->validated(),
        );

        return AccountMovementResource::make($payment->load(['account', 'category']));
    }

    public function collect(
        StoreAccountCollectionRequest $request,
        AccountMovement $accountMovement,
    ): AccountMovementResource {
        $collection = $this->accountMovements->registerCollection(
            $request->user(),
            $accountMovement,
            $request->validated(),
        );

        return AccountMovementResource::make($collection->load(['account', 'category']));
    }

    public function show(Request $request, AccountMovement $accountMovement): AccountMovementResource
    {
        abort_unless($accountMovement->user_id === $request->user()->id, 404);

        return AccountMovementResource::make($accountMovement->load(['account', 'category']));
    }
}
