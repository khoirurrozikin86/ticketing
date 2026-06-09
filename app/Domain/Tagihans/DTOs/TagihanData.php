<?php

namespace App\Domain\Tagihans\DTOs;

class TagihanData
{
    public function __construct(
        public string  $no_tagihan,
        public string  $id_bulan,        // '01'..'12'
        public int     $tahun,
        public string  $id_pelanggan,    // pelanggans.id_pelanggan
        public float   $jumlah_tagihan,
        public string  $status = 'belum', // belum|lunas
        public ?string $tgl_bayar = null, // Y-m-d
        public ?int    $user_id = null,
        public ?string $remark1 = null,
        public ?string $remark2 = null,
        public ?string $remark3 = null,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            no_tagihan: (string)($a['no_tagihan'] ?? ''),
            id_bulan: (string)($a['id_bulan'] ?? ''),
            tahun: (int)($a['tahun'] ?? date('Y')),
            id_pelanggan: (string)($a['id_pelanggan'] ?? ''),
            jumlah_tagihan: (float)str_replace(',', '', (string)($a['jumlah_tagihan'] ?? 0)),
            status: (string)($a['status'] ?? 'belum'),
            tgl_bayar: $a['tgl_bayar'] ?? null,
            user_id: isset($a['user_id']) ? (int)$a['user_id'] : null,
            remark1: $a['remark1'] ?? null,
            remark2: $a['remark2'] ?? null,
            remark3: $a['remark3'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'no_tagihan'     => $this->no_tagihan,
            'id_bulan'       => $this->id_bulan,
            'tahun'          => $this->tahun,
            'id_pelanggan'   => $this->id_pelanggan,
            'jumlah_tagihan' => $this->jumlah_tagihan,
            'status'         => $this->status,
            'tgl_bayar'      => $this->tgl_bayar,
            'user_id'        => $this->user_id,
            'remark1'        => $this->remark1,
            'remark2'        => $this->remark2,
            'remark3'        => $this->remark3,
        ];
    }
}
