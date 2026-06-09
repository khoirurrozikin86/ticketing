<?php

namespace App\Domain\Monitoring\Queries;

use App\Models\Pelanggan;

class GetPelangganByServer
{
    public function handle(int $serverId)
    {
        return Pelanggan::query()
            ->select(['id', 'nama', 'ip_router', 'ip_parent_router'])
            ->where('id_server', $serverId)
            ->orderBy('nama')
            ->get();
    }
}
