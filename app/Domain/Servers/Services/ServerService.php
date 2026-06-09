<?php

namespace App\Domain\Servers\Services;

use App\Domain\Servers\DTOs\ServerData;
use App\Domain\Servers\Actions\{
    CreateServerAction,
    UpdateServerAction,
    DeleteServerAction
};
use App\Models\Server;

class ServerService
{
    public function __construct(
        protected CreateServerAction $create,
        protected UpdateServerAction $update,
        protected DeleteServerAction $delete,
    ) {}

    public function create(array $payload): Server
    {
        return ($this->create)(ServerData::fromArray($payload));
    }

    public function update(Server $server, array $payload): Server
    {
        return ($this->update)($server, ServerData::fromArray($payload));
    }

    public function delete(Server $server): void
    {
        ($this->delete)($server);
    }
}
