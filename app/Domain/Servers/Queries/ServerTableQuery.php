<?php

namespace App\Domain\Servers\Queries;

use App\Models\Server;
use Illuminate\Database\Eloquent\Builder;

class ServerTableQuery
{
    public function builder(): Builder
    {
        return Server::query()->select([
            'id',
            'ip',
            'user',
            'lokasi',
            'no_int',
            'mikrotik',
            'remark1',
            'remark2',
            'remark3',
            'updated_at',
            'created_at'
        ]);
        // password sengaja tidak dipilih untuk tabel
    }
}
