<?php

namespace App\Domain\Monitoring\Services;

interface MikrotikClientInterface
{
    /**
     * Open a connection and run a callback; returns $default on failure.
     *
     * @param array   $conf ['host','user','pass','port','ssl', ...]
     * @param \Closure $callback receives the low-level API client (RouterosAPI)
     * @param mixed   $default
     * @return mixed
     */
    public function withConnection(array $conf, \Closure $callback, $default = null);

    /**
     * Ping a single target via an existing MikroTik API connection.
     *
     * NOTE: $mikrotik is the low-level client instance (RouterosAPI).
     *
     * @param mixed       $mikrotik     RouterosAPI instance
     * @param string      $target       IP/host to ping
     * @param string|null $srcAddress   Optional source address (e.g. gateway of subnet)
     * @param int         $count
     * @param int         $timeoutMs
     * @return array{ok:bool, latency:float|null}
     */
    public function pingOne($mikrotik, string $target, ?string $srcAddress = null, int $count = 2, int $timeoutMs = 1000): array;
}
