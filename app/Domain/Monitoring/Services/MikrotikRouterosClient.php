<?php

namespace App\Domain\Monitoring\Services;

use App\Services\RouterosAPI;
use Throwable;

class MikrotikRouterosClient implements MikrotikClientInterface
{
    public function __construct(private readonly RouterosAPI $api) {}

    public function withConnection(array $conf, \Closure $callback, $default = null)
    {
        try {
            $this->api->port     = $conf['port'] ?? 8728;
            $this->api->ssl      = $conf['ssl']  ?? false;
            $this->api->timeout  = 5;
            $this->api->attempts = 2;
            $this->api->delay    = 1;

            if (!$this->api->connect($conf['host'], $conf['user'], $conf['pass'])) {
                return $default;
            }
            return $callback($this->api);
        } catch (Throwable $e) {
            report($e);
            return $default;
        } finally {
            $this->api->disconnect();
        }
    }

    public function pingOne($mikrotik, string $target, ?string $srcAddress = null, int $count = 2, int $timeoutMs = 1000): array
    {
        $args = [
            'address'  => $target,
            'count'    => max(1, $count),
            'interval' => '1s',
            'timeout'  => $timeoutMs . 'ms',
        ];
        if ($srcAddress) {
            $args['src-address'] = $srcAddress;
        }

        // try /tool/ping first
        $rows   = $mikrotik->comm('/tool/ping', $args);
        $parsed = $this->parsePingRows($rows);

        // fallback to /ping if needed
        if ($parsed['ok'] === false && $parsed['latency'] === null) {
            $rows   = $mikrotik->comm('/ping', [
                'address'  => $target,
                'count'    => max(1, $count),
                'interval' => '00:00:01',
                'timeout'  => $timeoutMs,
            ]);
            $parsed = $this->parsePingRows($rows);
        }

        return $parsed; // ['ok'=>bool, 'latency'=>float|null]
    }

    /** @param mixed $rows */
    private function parsePingRows($rows): array
    {
        $ok = false;
        $lat = null;
        $receivedSum = null;

        foreach ((array)$rows as $r) {
            if (isset($r['time'])) {
                $ok = true;
                $t  = (float) str_replace(['ms', ' '], '', (string)$r['time']);
                $lat = is_null($lat) ? $t : min($lat, $t);
            }
            if (isset($r['avg-rtt'])) { // some ROS versions
                $ok = true;
                $t  = (float) str_replace(['ms', ' '], '', (string)$r['avg-rtt']);
                $lat = is_null($lat) ? $t : min($lat, $t);
            }
            if (isset($r['received'])) {
                $receivedSum = (int) $r['received'];
            }
        }

        if (!is_null($receivedSum)) {
            $ok = $receivedSum > 0;
        }

        return ['ok' => $ok, 'latency' => $lat];
    }
}
