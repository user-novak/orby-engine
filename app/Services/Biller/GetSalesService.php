<?php

namespace App\Services\Biller;

use App\Models\Biller;
use App\Enums\SaleType;
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
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('sale_date', [
                $request->date_from,
                $request->date_to
            ]);
        }

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

        if ($request->filled('status')) {

            if ($request->status === 'pending') {

                $query->where(function ($q) {
                    $q->where('sale_type', SaleType::CREDIT->value)
                        ->whereNull('payment_date');
                });
            }

            if ($request->status === 'paid') {

                $query->where(function ($q) {
                    $q->where('sale_type', SaleType::CASH->value)
                        ->orWhereNotNull('payment_date');
                });
            }
        }
    }
}
