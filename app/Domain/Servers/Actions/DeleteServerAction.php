<?php

namespace App\Domain\Servers\Actions;

use App\Models\Server;
use Illuminate\Support\Facades\DB;

class DeleteServerAction
{
    public function __invoke(Server $server): void
    {
        DB::transaction(fn() => $server->delete());
    }
}
