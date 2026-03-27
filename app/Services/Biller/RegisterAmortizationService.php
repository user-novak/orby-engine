<?php

namespace App\Services\Biller;

use App\Models\BillerPayment;
use App\Models\BillerPaymentAmortization;
use Illuminate\Support\Facades\DB;
use DomainException;

class RegisterAmortizationService
{
    public function execute(int $paymentId, array $data): BillerPaymentAmortization
    {
        return DB::transaction(function () use ($paymentId, $data) {

            $payment = BillerPayment::with('amortizations')
                ->findOrFail($paymentId);

            $paid = $payment->amortizations->sum('amount');
            $remaining = $payment->amount - $paid;

            if ($data['amount'] > $remaining) {
                throw new DomainException(
                    'El monto excede el saldo pendiente'
                );
            }

            $amortization = BillerPaymentAmortization::create([
                'biller_payment_id' => $payment->id,
                'client_id' => $payment->client_id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'] ?? now(),
            ]);

            return $amortization;
        });
    }
}