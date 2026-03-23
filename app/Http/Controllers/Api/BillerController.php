<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillerResource;
use App\Models\Client;
use App\Models\Storage;
use App\Models\Account;
use App\Traits\ApiResponse;
use App\Http\Requests\Biller\BillerRequest;
use Illuminate\Http\Request;
use App\Services\Biller\CreateSaleService;
use App\Services\Biller\GetSalesService;

class BillerController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CreateSaleService $createSaleService,
        protected GetSalesService $getSalesService
    ) {}

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
        try {

            $sale = $this->createSaleService->execute(
                $request->validated()
            );

            return $this->success(
                new BillerResource($sale),
                'Venta registrada correctamente'
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Throwable $e) {

            return $this->error(
                'Error al registrar la venta',
                500,
                $e->getMessage()
            );
        }
    }

    public function sales(Request $request)
    {
        try {

            $sales = $this->getSalesService->execute($request);

            return $this->success(
                $sales,
                'Ventas obtenidas correctamente'
            );
        } catch (\Throwable $e) {

            return $this->error(
                'Error al obtener las ventas',
                500,
                $e->getMessage()
            );
        }
    }
}
