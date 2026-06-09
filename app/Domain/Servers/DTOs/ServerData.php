<?php

namespace App\Domain\Servers\DTOs;

class ServerData
{
    public function __construct(
        public string $ip,
        public string $user,
        public ?string $password,
        public string $lokasi,
        public ?string $no_int = null,
        public ?string $mikrotik = null,
        public ?string $remark1 = null,
        public ?string $remark2 = null,
        public ?string $remark3 = null,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            ip: (string)($a['ip'] ?? ''),
            user: (string)($a['user'] ?? ''),
            password: array_key_exists('password', $a) ? (string)$a['password'] : null,
            lokasi: (string)($a['lokasi'] ?? ''),
            no_int: $a['no_int'] ?? null,
            mikrotik: $a['mikrotik'] ?? null,
            remark1: $a['remark1'] ?? null,
            remark2: $a['remark2'] ?? null,
            remark3: $a['remark3'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'ip'       => $this->ip,
            'user'     => $this->user,
            'password' => $this->password,
            'lokasi'   => $this->lokasi,
            'no_int'   => $this->no_int,
            'mikrotik' => $this->mikrotik,
            'remark1'  => $this->remark1,
            'remark2'  => $this->remark2,
            'remark3'  => $this->remark3,
        ];
    }
}
