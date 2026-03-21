<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillerResource;
use App\Models\Client;
use App\Models\Storage;
use App\Models\Account;
use App\Traits\ApiResponse;
use App\Enums\SaleType;
use App\Http\Requests\Biller\BillerRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Biller;
use App\Models\BillerItem;

class BillerController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data = [
            'clients' => Client::select('id', 'name')->get(),
            'storages' => Storage::select([
                'id',
                'description',
                'measure_unity',
                'unit_price',
                'percentage_distributor',
                'price_distributor',
                'percentage_major',
                'price_major',
                'percentage_general',
                'price_general',
                'created_at',
                'updated_at',
            ])->get(),
            'accounts' => Account::select('id', 'name')->get(),
        ];

        return $this->success(
            new BillerResource($data),
            'Datos del facturador obtenidos correctamente'
        );
    }

    public function store(BillerRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {

            $subtotal = 0;

            foreach ($data['items'] as $item) {

                $storage = Storage::findOrFail($item['storage_id']);

                if ($storage->stock < $item['quantity']) {
                    return $this->error(
                        "Stock insuficiente para {$storage->description}"
                    );
                }

                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;

            $biller = Biller::create([
                'sale_date' => $data['sale_date'],
                'place' => $data['place'] ?? null,
                'sale_type' => $data['sale_type'],
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'client_id' => $data['client_id'],
                'account_id' => $data['account_id'],
            ]);

            foreach ($data['items'] as $item) {

                $storage = Storage::findOrFail($item['storage_id']);

                $itemSubtotal = $item['quantity'] * $item['unit_price'];

                BillerItem::create([
                    'biller_id' => $biller->id,
                    'storage_id' => $storage->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);

                $storage->decrement('stock', $item['quantity']);
                $storage->increment('output', $item['quantity']);
            }

            if ($data['sale_type'] === SaleType::CASH->value) {

                $account = Account::findOrFail($data['account_id']);

                $account->increment('balance', $total);
            }

            return $this->success(
                $biller->load('items'),
                'Venta registrada correctamente'
            );
        });
    }
}
