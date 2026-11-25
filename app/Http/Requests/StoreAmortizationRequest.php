<?php

namespace App\Http\Requests;

use App\Models\Biller;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAmortizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $biller = $this->route('biller');

        return $biller instanceof Biller
            && $biller->user_id === $this->user()->id;
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
                // Regla de negocio: no se puede amortizar más de lo que
                // realmente se debe. La validamos aquí (contra el registro
                // 'cuenta_por_cobrar' vigente de la factura) para que el
                // controller/Service reciban el dato ya garantizado válido.
                function (string $attribute, mixed $value, Closure $fail): void {
                    /** @var Biller $biller */
                    $biller = $this->route('biller');

                    $pending = $biller->accountMovements()
                        ->where('status', 'cuenta_por_cobrar')
                        ->first();

                    if (! $pending) {
                        $fail('Esta factura no tiene una cuenta por cobrar pendiente.');

                        return;
                    }

                    if ($value > $pending->amount) {
                        $fail("El monto amortizado no puede superar la deuda pendiente ({$pending->amount}).");
                    }
                },
            ],
        ];
    }
}
