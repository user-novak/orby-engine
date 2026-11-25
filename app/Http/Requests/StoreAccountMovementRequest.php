<?php

namespace App\Http\Requests;

use App\Enums\AccountMovementStatus;
use App\Enums\AccountMovementType;
use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'movement_date' => ['required', 'date'],
            'type' => ['required', Rule::enum(AccountMovementType::class)],
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'description' => ['nullable', 'string'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function (Builder $query) {
                    $query
                        ->where('status', CategoryStatus::Activo->value)
                        ->whereIn('type', [$this->input('type'), CategoryType::Ambos->value])
                        ->where(function (Builder $owner) {
                            $owner
                                ->whereNull('user_id')
                                ->orWhere('user_id', $this->user()->id);
                        });
                }),
            ],
            'account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $this->user()->id),
                Rule::requiredIf(fn () => in_array($this->input('status'), $this->settledStatuses(), true)),
                Rule::prohibitedIf(fn () => in_array($this->input('status'), $this->futureStatuses(), true)),
            ],
        ];
    }

    private function allowedStatuses(): array
    {
        return match ($this->input('type')) {
            AccountMovementType::Ingreso->value => [
                AccountMovementStatus::CuentaCobrada->value,
                AccountMovementStatus::CuentaPorCobrar->value,
            ],
            AccountMovementType::Egreso->value => [
                AccountMovementStatus::CuentaPagada->value,
                AccountMovementStatus::CuentaPorPagar->value,
            ],
            default => [],
        };
    }

    private function settledStatuses(): array
    {
        return [
            AccountMovementStatus::CuentaCobrada->value,
            AccountMovementStatus::CuentaPagada->value,
        ];
    }

    private function futureStatuses(): array
    {
        return [
            AccountMovementStatus::CuentaPorCobrar->value,
            AccountMovementStatus::CuentaPorPagar->value,
        ];
    }
}
