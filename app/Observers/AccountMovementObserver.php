<?php

namespace App\Observers;

use App\Enums\AccountMovementType;
use App\Models\Account;
use App\Models\AccountMovement;
use Illuminate\Support\Facades\DB;

class AccountMovementObserver
{
    public function created(AccountMovement $accountMovement): void
    {
        if ($accountMovement->account_id === null) {
            return;
        }

        $type = $accountMovement->type instanceof AccountMovementType
            ? $accountMovement->type
            : AccountMovementType::from($accountMovement->type);

        $sign = match ($type) {
            AccountMovementType::Ingreso => 1,
            AccountMovementType::Egreso => -1,
        };

        DB::transaction(function () use ($accountMovement, $sign) {
            $account = Account::whereKey($accountMovement->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $account->increment('amount', $sign * (float) $accountMovement->amount);
        });
    }
}
