<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Requests\IndexClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients)
    {
    }

    public function index(IndexClientRequest $request): AnonymousResourceCollection
    {
        return ClientResource::collection(
        Client::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->latest('id')
            ->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreClientRequest $request): ClientResource
    {
        $client = $this->clients->create($request->user(), $request->validated());

        return ClientResource::make($client);
    }

    public function show(Request $request, Client $client): ClientResource
    {
        abort_unless($client->user_id === $request->user()->id, 404);

        return ClientResource::make($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $client = $this->clients->update($client, $request->validated());

        return ClientResource::make($client);
    }

    public function destroy(Request $request, Client $client): Response
    {
        abort_unless($client->user_id === $request->user()->id, 404);

        $this->clients->delete($client);

        return response()->noContent();
    }
}
