<?php

namespace App\Domain\Bulans\DTOs;

class BulanData
{
    public function __construct(
        public string $id_bulan,
        public string $bulan,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            id_bulan: strtoupper(trim((string)($a['id_bulan'] ?? ''))), // ex: '01'..'12'
            bulan: trim((string)($a['bulan'] ?? '')),
        );
    }

    public function toArray(): array
    {
        return ['id_bulan' => $this->id_bulan, 'bulan' => $this->bulan];
    }
}
