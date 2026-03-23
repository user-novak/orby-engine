<?php

namespace App\Services\Biller;

use App\Models\Biller;
use App\Models\BillerItem;
use App\Models\BillerPayment;
use App\Models\Storage;
use App\Models\Account;
use App\Enums\SaleType;
use Illuminate\Support\Facades\DB;
use DomainException;

class CreateSaleService
{
    public function execute(array $data): Biller
    {
        return DB::transaction(function () use ($data) {

            $items = collect($data['items']);

            $storages = Storage::whereIn('id', $items->pluck('storage_id'))
                ->get()
                ->keyBy('id');

            $subtotal = 0;

            foreach ($items as $item) {

                $storage = $storages->get($item['storage_id']);

                if (!$storage) {
                    throw new DomainException(
                        "Producto no encontrado: {$item['storage_id']}"
                    );
                }

                if ($storage->stock < $item['quantity']) {
                    throw new DomainException(
                        "Stock insuficiente para {$storage->description}"
                    );
                }

                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $igv = $data['igv'];
            $total = $subtotal + $igv;

            $biller = Biller::create([
                'sale_date' => $data['sale_date'],
                'payment_date' => $data['payment_date'] ?? null,
                'place' => $data['place'] ?? null,
                'sale_type' => $data['sale_type'],
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'client_id' => $data['client_id'],
                'account_id' => $data['account_id'],
            ]);

            foreach ($items as $item) {

                $storage = $storages->get($item['storage_id']);

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

            $this->handlePayments($biller, $data, $total);

            return $biller->load('items.storage');
        });
    }

    private function handlePayments($biller, $data, $total): void
    {
        if ($data['sale_type'] === SaleType::CREDIT->value) {

            BillerPayment::create([
                'biller_id' => $biller->id,
                'client_id' => $data['client_id'],
                'amount' => $total,
                'payment_date' => $data['payment_date'],
            ]);
        }

        if ($data['sale_type'] === SaleType::CASH->value) {

            $account = Account::findOrFail($data['account_id']);
            $account->increment('amount', $total);
        }
    }
}
