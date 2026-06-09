<?php

namespace App\Domain\Pembayarans\DTOs;

class PaymentData
{
    public function __construct(
        public int $tagihan_id,
        public float $amount,
        public string $paid_at,         // Y-m-d
        public ?string $method = null,
        public ?string $ref_no = null,
        public ?string $note = null,
        public ?int $user_id = null,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            tagihan_id: (int)$a['tagihan_id'],
            amount: (float)str_replace(',', '', (string)$a['amount']),
            paid_at: (string)$a['paid_at'],
            method: $a['method'] ?? null,
            ref_no: $a['ref_no'] ?? null,
            note: $a['note'] ?? null,
            user_id: isset($a['user_id']) ? (int)$a['user_id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'tagihan_id' => $this->tagihan_id,
            'amount' => $this->amount,
            'paid_at' => $this->paid_at,
            'method' => $this->method,
            'ref_no' => $this->ref_no,
            'note' => $this->note,
            'user_id' => $this->user_id,
        ];
    }
}
