<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;

class ClientService
{
    public function create(User $user, array $data): Client
    {
        return $user->clients()->create($data);
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
