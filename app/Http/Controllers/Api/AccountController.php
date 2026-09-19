<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accounts)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return AccountResource::collection(
            Account::query()->latest()->paginate()
        );
    }

    public function store(StoreAccountRequest $request): AccountResource
    {
        $account = $this->accounts->create($request->validated());

        return AccountResource::make($account);
    }

    public function show(Account $account): AccountResource
    {
        return AccountResource::make($account);
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $account = $this->accounts->update($account, $request->validated());

        return AccountResource::make($account);
    }

    public function destroy(Account $account): Response
    {
        $this->accounts->delete($account);

        return response()->noContent();
    }
}
