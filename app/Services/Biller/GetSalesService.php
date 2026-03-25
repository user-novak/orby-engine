<?php

namespace App\Services\Biller;

use App\Models\Biller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetSalesService
{
    public function execute(Request $request)
    {
        $query = Biller::query()
            ->with([
                'client:id,name',
                'account:id,name',
                'items.storage:id,description'
            ]);

        $this->applyFilters($query, $request);

        return $query
            ->orderBy('sale_date', 'desc')
            ->paginate($request->get('per_page', 20));
    }

    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->where('sale_date', '>=', Carbon::parse($request->date_from)->startOfDay());
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->where('sale_date', '<=', Carbon::parse($request->date_to)->endOfDay());
        });

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('storage_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('storage_id', $request->storage_id);
            });
        }

        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }
    }
}
