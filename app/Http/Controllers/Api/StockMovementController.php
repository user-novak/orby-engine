<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockMovementService $stockMovements)
    {
    }

    /**
     * Historial de movimientos (kardex). El filtrado por query params es
     * lectura pura, no "lógica de negocio" — por eso vive directo aquí y
     * no se delega al Service (el Service es para mutaciones/reglas).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $movements = StockMovement::query()
            ->with('product')
            ->when(
                $request->filled('product_id'),
                fn ($query) => $query->where('product_id', $request->integer('product_id'))
            )
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', $request->string('type'))
            )
            ->latest('movement_date')
            ->paginate();

        return StockMovementResource::collection($movements);
    }

    public function show(StockMovement $stockMovement): StockMovementResource
    {
        return StockMovementResource::make($stockMovement->load('product'));
    }

    /**
     * Solo para entradas manuales (compra/ajuste de inventario). No hay
     * update() ni destroy(): el ledger de stock es inmutable, igual que
     * decidiste con el Observer.
     */
    public function store(StoreStockMovementRequest $request): StockMovementResource
    {
        $stockMovement = $this->stockMovements->registerManualEntry($request->validated());

        return StockMovementResource::make($stockMovement);
    }
}
