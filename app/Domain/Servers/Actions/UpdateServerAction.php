<?php

namespace App\Domain\Servers\Actions;

use App\Domain\Servers\DTOs\ServerData;
use App\Models\Server;
use Illuminate\Support\Facades\DB;

class UpdateServerAction
{
    public function __invoke(Server $server, ServerData $data): Server
    {
        $payload = $data->toArray();
        $payload['ip'] = trim($payload['ip']);
        $payload['user'] = trim($payload['user']);

        // Jika password null atau empty string → jangan ubah password
        if (!array_key_exists('password', $payload) || $payload['password'] === '' || $payload['password'] === null) {
            unset($payload['password']);
        }

        return DB::transaction(function () use ($server, $payload) {
            $server->update($payload);
            return $server->refresh();
        });
    }
}
