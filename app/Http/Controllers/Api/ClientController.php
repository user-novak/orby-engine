<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return ClientResource::collection(
            Client::query()->latest()->paginate()
        );
    }

    public function store(StoreClientRequest $request): ClientResource
    {
        $client = $this->clients->create($request->validated());

        return ClientResource::make($client);
    }

    public function show(Client $client): ClientResource
    {
        return ClientResource::make($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $client = $this->clients->update($client, $request->validated());

        return ClientResource::make($client);
    }

    public function destroy(Client $client): Response
    {
        $this->clients->delete($client);

        return response()->noContent();
    }
}
