<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillerPayment;
use App\Traits\ApiResponse;
use App\Http\Requests\Biller\AmortizationRequest;
use App\Services\Biller\RegisterAmortizationService;
use DomainException;

class BillerPaymentAmortizationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RegisterAmortizationService $registerAmortizationService
    ) {}

    public function index($paymentId)
    {
        try {

            $payment = BillerPayment::with('amortizations')
                ->findOrFail($paymentId);

            return $this->success(
                $payment->amortizations,
                'Amortizaciones obtenidas correctamente'
            );
        } catch (\Throwable $e) {

            return $this->error(
                'Error al obtener amortizaciones',
                500,
                $e->getMessage()
            );
        }
    }

    public function store(AmortizationRequest $request, $paymentId)
    {
        try {

            $amortization = $this->registerAmortizationService->execute(
                $paymentId,
                $request->validated()
            );

            return $this->success(
                $amortization,
                'Amortización registrada correctamente'
            );
        } catch (DomainException $e) {

            return $this->error($e->getMessage(), 400);
        } catch (\Throwable $e) {

            return $this->error(
                'Error al registrar la amortización',
                500,
                $e->getMessage()
            );
        }
    }
}
