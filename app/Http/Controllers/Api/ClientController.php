<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Requests\BulkStoreClientRequest;
use App\Traits\ApiResponse;
use App\Models\Client;

class ClientController extends Controller
{

    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(
            Client::all(),
            'Clientes obtenidos correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        $client = Client::create($request->validated());

        return $this->success(
            $client,
            'Cliente registrado correctamente',
            201
        );
    }

    /**
     * Store masive resource in storage.
     */
    public function bulkStore(BulkStoreClientRequest $request)
    {
        Client::insert($request->validated()['clients']);

        return $this->success(
            null,
            'Clientes registrados correctamente',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return $this->success(
            $client,
            'Cliente obtenido correctamente'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return $this->success(
            $client,
            'Cliente actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return $this->success(
            null,
            'Cliente eliminado correctamente',
            204
        );
    }
}
