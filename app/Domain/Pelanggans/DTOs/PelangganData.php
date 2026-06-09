<?php

namespace App\Domain\Pelanggans\DTOs;

class PelangganData
{
    public function __construct(
        public string  $id_pelanggan,
        public string  $nama,
        public ?string $alamat = null,
        public ?string $no_hp = null,
        public ?string $email = null,
        public ?string $password = null, // null/'' = abaikan saat update
        public ?int    $id_paket = null,
        public ?string $ip_router = null,
        public ?string $ip_parent_router = null,
        public ?string $remark1 = null,
        public ?string $remark2 = null,
        public ?string $remark3 = null,
        public ?int    $id_server = null,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            id_pelanggan: (string)($a['id_pelanggan'] ?? ''),
            nama: (string)($a['nama'] ?? ''),
            alamat: $a['alamat'] ?? null,
            no_hp: $a['no_hp'] ?? null,
            email: $a['email'] ?? null,
            password: array_key_exists('password', $a) ? (string)$a['password'] : null,
            id_paket: isset($a['id_paket']) ? (int)$a['id_paket'] : null,
            ip_router: $a['ip_router'] ?? null,
            ip_parent_router: $a['ip_parent_router'] ?? null,
            remark1: $a['remark1'] ?? null,
            remark2: $a['remark2'] ?? null,
            remark3: $a['remark3'] ?? null,
            id_server: isset($a['id_server']) ? (int)$a['id_server'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id_pelanggan'    => $this->id_pelanggan,
            'nama'            => $this->nama,
            'alamat'          => $this->alamat,
            'no_hp'           => $this->no_hp,
            'email'           => $this->email,
            'password'        => $this->password,
            'id_paket'        => $this->id_paket,
            'ip_router'       => $this->ip_router,
            'ip_parent_router' => $this->ip_parent_router,
            'remark1'         => $this->remark1,
            'remark2'         => $this->remark2,
            'remark3'         => $this->remark3,
            'id_server'       => $this->id_server,
        ];
    }
}
