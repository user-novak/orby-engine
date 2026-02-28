<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\BulkStoreAccountRequest;
use App\Traits\ApiResponse;
use App\Models\Account;

class AccountController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(
            Account::all(),
            'Cuentas obtenidas correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccountRequest $request)
    {
        $account = Account::create($request->validated());

        return $this->success(
            $account,
            'Cuenta registrada correctamente',
            201
        );
    }

    /**
     * Store masive resource in storage.
     */
    public function bulkStore(BulkStoreAccountRequest $request)
    {
        Account::insert($request->validated()['accounts']);

        return $this->success(
            null,
            'Cuentas registradas correctamente',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        return $this->success(
            $account,
            'Cuenta obtenida correctamente'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());

        return $this->success(
            $account,
            'Cuenta actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->delete();

        return $this->success(
            null,
            'Cuenta eliminada correctamente',
            204
        );
    }
}
