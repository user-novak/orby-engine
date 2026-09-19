<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillerItemResource;
use App\Models\BillerItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller de solo lectura, a propósito: un BillerItem siempre se crea
 * junto con su Biller y su StockMovement (dentro de la misma transacción
 * de BillerService, que construiremos más adelante). No exponemos
 * store/update/destroy aquí porque crear o editar un item suelto rompería
 * la consistencia entre biller_items.subtotal, billers.total y el stock
 * del producto.
 */
class BillerItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = BillerItem::query()
            ->with('product')
            ->when(
                $request->filled('biller_id'),
                fn ($query) => $query->where('biller_id', $request->integer('biller_id'))
            )
            ->when(
                $request->filled('product_id'),
                fn ($query) => $query->where('product_id', $request->integer('product_id'))
            )
            ->latest()
            ->paginate();

        return BillerItemResource::collection($items);
    }

    public function show(BillerItem $billerItem): BillerItemResource
    {
        return BillerItemResource::make($billerItem->load('product'));
    }
}
