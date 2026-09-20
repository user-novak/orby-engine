<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBillerRequest;
use App\Http\Resources\BillerResource;
use App\Models\Biller;
use App\Services\BillerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BillerController extends Controller
{
    public function __construct(private readonly BillerService $billers)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $billers = Biller::query()
            ->with('client')
            ->when(
                $request->filled('client_id'),
                fn ($query) => $query->where('client_id', $request->integer('client_id'))
            )
            ->when(
                $request->filled('sale_type'),
                fn ($query) => $query->where('sale_type', $request->string('sale_type'))
            )
            ->latest('sale_date')
            ->paginate();

        return BillerResource::collection($billers);
    }

    public function store(StoreBillerRequest $request): BillerResource
    {
        $biller = $this->billers->create($request->validated());

        return BillerResource::make($biller);
    }

    public function show(Biller $biller): BillerResource
    {
        return BillerResource::make(
            $biller->load(['client', 'items.product', 'accountMovements'])
        );
    }

    // Sin update() ni destroy(): modificar o anular una venta ya creada
    // implica reversar stock y movimientos de cuenta. Eso amerita su propia
    // acción de negocio explícita (ej. "anular factura"), igual que hicimos
    // con AmortizationController — no un PUT/DELETE genérico que invitaría
    // a editar campos sueltos y dejar el ledger inconsistente.
}
