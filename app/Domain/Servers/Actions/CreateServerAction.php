<?php

namespace App\Domain\Servers\Actions;

use App\Domain\Servers\DTOs\ServerData;
use App\Models\Server;
use Illuminate\Support\Facades\DB;

class CreateServerAction
{
    public function __invoke(ServerData $data): Server
    {
        $payload = $data->toArray();
        // normalisasi kecil
        $payload['ip'] = trim($payload['ip']);
        $payload['user'] = trim($payload['user']);
        if ($payload['password'] === '') $payload['password'] = null;

        return DB::transaction(fn() => Server::create($payload));
    }
}
