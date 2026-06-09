<?php

namespace App\Domain\Monitoring\Queries;

use App\Models\Pelanggan;
use Illuminate\Support\Collection;

class GetRoutersByServer
{
    /** @return Collection<int, object{nama:string,ip_router:?string,ip_parent_router:?string}> */
    public function handle(int $serverId): Collection
    {
        return Pelanggan::query()
            ->select(['id', 'nama', 'ip_router', 'ip_parent_router'])
            ->where('id_server', $serverId)
            ->orderBy('nama')
            ->get();
    }
}
