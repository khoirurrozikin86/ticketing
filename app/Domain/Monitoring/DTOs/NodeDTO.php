<?php

namespace App\Domain\Monitoring\DTOs;

class NodeDTO
{
    public function __construct(
        public string $label,
        public ?string $ip,
        public string $status = 'unknown', // up|down|unknown
        public ?float $latency = null,
        /** @var NodeDTO[] */
        public array $children = []
    ) {}

    public static function make(string $label, ?string $ip): self
    {
        return new self(label: $label, ip: $ip);
    }

    public function toArray(): array
    {
        return [
            'label'   => $this->label,
            'ip'      => $this->ip,
            'status'  => $this->status,
            'latency' => $this->latency,
            'children' => array_map(fn(self $n) => $n->toArray(), $this->children),
        ];
    }
}
