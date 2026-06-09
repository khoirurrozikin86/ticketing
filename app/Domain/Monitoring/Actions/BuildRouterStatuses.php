<?php

namespace App\Domain\Monitoring\Actions;

use App\Domain\Monitoring\DTOs\RouterStatusDTO;
use App\Domain\Monitoring\Queries\GetRoutersByServer;
use App\Domain\Monitoring\Services\MikrotikClientInterface;
use App\Models\Server;
use App\Support\ServerCreds;

class BuildRouterStatuses
{
    public function __construct(
        private readonly GetRoutersByServer $query,
        private readonly MikrotikClientInterface $mt
    ) {}

    /** @return array{routerStatus: array<int,array>, server: array} */
    public function handle(int $serverId): array
    {
        $server = Server::findOrFail($serverId);
        $creds  = ServerCreds::from($server);
        $rows   = $this->query->handle($serverId);

        $result = $this->mt->withConnection($creds, function ($conn) use ($rows) {
            $list = [];
            foreach ($rows as $r) {
                $ip = trim((string) $r->ip_router);
                $parentIp = trim((string) $r->ip_parent_router);

                if ($ip !== '') {
                    $ping = $this->mt->pingOne($conn, $ip, 1, 1000);
                    $status = $ping['ok'] ? 'green' : 'red';
                    $lat    = $ping['latency'];
                } else {
                    $status = 'red';
                    $lat    = null;
                }

                $dto = new RouterStatusDTO(
                    name: $r->nama,
                    ip: $ip ?: null,
                    parent: $parentIp ?: null,
                    status: $status,
                    latencyMs: $lat
                );

                $item = $dto->toArray();
                $item['pid'] = (int) $r->id; // id unik per pelanggan → penting utk tree
                $list[] = $item;
            }
            return $list;
        }, default: []);

        return [
            'routerStatus' => $result,
            'server' => [
                'id'     => $server->id,
                'ip'     => $server->ip,
                'user'   => $server->user,
                'lokasi' => $server->lokasi,
            ],
        ];
    }
}
