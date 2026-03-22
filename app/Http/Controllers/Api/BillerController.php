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
use App\Models\BillerPayment;

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

        try {

            DB::beginTransaction();

            foreach ($data['items'] as $item) {

                $storage = Storage::findOrFail($item['storage_id']);

                if ($storage->stock < $item['quantity']) {
                    DB::rollBack();

                    return $this->error(
                        "Stock insuficiente para {$storage->description}",
                        400
                    );
                }
            }

            $biller = Biller::create([
                'sale_date' => $data['sale_date'],
                'payment_date' => $data['payment_date'] ?? null,
                'place' => $data['place'] ?? null,
                'sale_type' => $data['sale_type'],
                'subtotal' => $data['subtotal'],
                'igv' => $data['igv'],
                'total' => $data['total'],
                'client_id' => $data['client_id'],
                'account_id' => $data['account_id'],
            ]);

            foreach ($data['items'] as $item) {

                $storage = Storage::findOrFail($item['storage_id']);

                BillerItem::create([
                    'biller_id' => $biller->id,
                    'storage_id' => $storage->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $storage->decrement('stock', $item['quantity']);
                $storage->increment('output', $item['quantity']);
            }

            if ($data['sale_type'] === SaleType::CREDIT->value) {

                BillerPayment::create([
                    'biller_id' => $biller->id,
                    'client_id' => $data['client_id'],
                    'amount' => $data['total'],
                    'payment_date' => $data['payment_date'],
                ]);
            }

            if ($data['sale_type'] === SaleType::CASH->value) {

                $account = Account::findOrFail($data['account_id']);

                $account->increment('balance', $data['total']);
            }

            DB::commit();

            return $this->success(
                $biller->load('items.storage'),
                'Venta registrada correctamente'
            );
        } catch (\Throwable $e) {

            DB::rollBack();

            return $this->error(
                'Error al registrar la venta',
                500,
                $e->getMessage()
            );
        }
    }
}
