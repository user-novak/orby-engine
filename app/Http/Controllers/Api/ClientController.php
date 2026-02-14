<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Client::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|string|unique:clients,dni',
            'name' => 'required|string',
            'ruc' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    /**
     * Store masive resource in storage.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array|min:1',
            'clients.*.dni' => 'nullable|string|distinct',
            'clients.*.name' => 'required|string',
            'clients.*.ruc' => 'nullable|string',
            'clients.*.phone' => 'nullable|string',
            'clients.*.address' => 'nullable|string',
        ]);

        $clients = Client::insert($validated['clients']);

        return response()->json([
            'message' => 'Clientes registrados correctamente'
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'dni' => 'sometimes|string|unique:clients,dni,' . $client->id,
            'name' => 'sometimes|string',
            'ruc' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $client->update($validated);

        return response()->json($client);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(null, 204);
    }
}
