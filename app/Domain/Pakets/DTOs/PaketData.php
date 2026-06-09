<?php

namespace App\Domain\Pakets\DTOs;

class PaketData
{
    public function __construct(
        public string $id_paket,
        public string $nama,
        public float  $harga,
        public string $kecepatan,
        public int    $durasi,
        public ?string $remark1 = null,
        public ?string $remark2 = null,
        public ?string $remark3 = null,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            id_paket: $a['id_paket'],
            nama: $a['nama'],
            harga: (float) $a['harga'],
            kecepatan: $a['kecepatan'],
            durasi: (int) $a['durasi'],
            remark1: $a['remark1'] ?? null,
            remark2: $a['remark2'] ?? null,
            remark3: $a['remark3'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id_paket'  => $this->id_paket,
            'nama'      => $this->nama,
            'harga'     => $this->harga,
            'kecepatan' => $this->kecepatan,
            'durasi'    => $this->durasi,
            'remark1'   => $this->remark1,
            'remark2'   => $this->remark2,
            'remark3'   => $this->remark3,
        ];
    }
}
