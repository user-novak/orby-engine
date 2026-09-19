<?php

namespace App\Services;

use App\Models\Account;

class AccountService
{
    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function update(Account $account, array $data): Account
    {
        $account->update($data);

        return $account->refresh();
    }

    public function delete(Account $account): void
    {
        $account->delete();
    }
}
