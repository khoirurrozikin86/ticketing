<?php

namespace App\Domain\Monitoring\Queries;

use App\Models\Server;

class GetServerOptions
{
    /** @return array<int, array{id:int,label:string}> */
    public function handle(): array
    {
        return Server::query()
            ->select(['id', 'ip', 'lokasi', 'remark1'])
            ->orderBy('lokasi')
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'label' => ($s->remark1 ?: $s->ip) . ($s->lokasi ? " — {$s->lokasi}" : ''),
            ])
            ->all();
    }
}
