<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\IndexAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accounts)
    {
    }

    public function index(IndexAccountRequest $request): AnonymousResourceCollection
    {
        return AccountResource::collection(
            Account::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->latest('id')
                ->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreAccountRequest $request): AccountResource
    {
        $account = $this->accounts->create($request->user(), $request->validated());

        return AccountResource::make($account);
    }

    public function show(Request $request, Account $account): AccountResource
    {
        abort_unless($account->user_id === $request->user()->id, 404);

        return AccountResource::make($account);
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $account = $this->accounts->update($account, $request->validated());

        return AccountResource::make($account);
    }

    public function destroy(Request $request, Account $account): Response
    {
        abort_unless($account->user_id === $request->user()->id, 404);

        $this->accounts->delete($account);

        return response()->noContent();
    }
}
