<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->refresh();
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
