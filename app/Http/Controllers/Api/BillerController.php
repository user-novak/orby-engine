<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillerResource;
use App\Models\Client;
use App\Models\Storage;
use App\Models\Account;
use App\Traits\ApiResponse;

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
}
