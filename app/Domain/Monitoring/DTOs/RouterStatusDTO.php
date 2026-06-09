<?php

namespace App\Domain\Monitoring\DTOs;

class RouterStatusDTO
{
    public function __construct(
        public string $name,
        public ?string $ip,
        public ?string $parent,
        public string $status,    // green|red
        public ?float $latencyMs  // null kalau gagal
    ) {}

    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'ip'     => $this->ip,
            'parent' => $this->parent,
            'status' => $this->status,
            'latency' => $this->latencyMs,
        ];
    }
}
