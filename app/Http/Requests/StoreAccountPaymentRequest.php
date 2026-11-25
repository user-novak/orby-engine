<?php

namespace App\Http\Requests;

use App\Enums\AccountMovementStatus;
use App\Enums\AccountMovementType;
use App\Models\AccountMovement;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $movement = $this->route('accountMovement');

        return $movement instanceof AccountMovement
            && $movement->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id),
            ],
            'description' => ['nullable', 'string'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function (string $attribute, mixed $value, Closure $fail): void {
                    /** @var AccountMovement $movement */
                    $movement = $this->route('accountMovement');

                    if (
                        $movement->type !== AccountMovementType::Egreso->value
                        || $movement->status !== AccountMovementStatus::CuentaPorPagar->value
                    ) {
                        $fail('El movimiento no corresponde a una cuenta por pagar pendiente.');

                        return;
                    }

                    if ((float) $value > (float) $movement->amount) {
                        $fail("El monto no puede superar la deuda pendiente ({$movement->amount}).");
                    }
                },
            ],
        ];
    }
}
