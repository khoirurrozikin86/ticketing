<?php

namespace App\Domain\Pembayarans\Services;

use App\Domain\Pembayarans\DTOs\PaymentData;
use App\Domain\Pembayarans\Actions\{CreatePaymentAction, DeletePaymentAction};
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected CreatePaymentAction $create,
        protected DeletePaymentAction $delete,
    ) {}

    public function create(array $payload): Pembayaran
    {
        return ($this->create)(PaymentData::fromArray($payload));
    }




    /**
     * 🔥 MULTIPLE PAYMENT
     * Banyak tagihan → 1 pembayaran
     */
    public function payMultiple(array $payload): Pembayaran
    {
        return DB::transaction(function () use ($payload) {

            $tagihans = Tagihan::whereIn('id', $payload['tagihan_ids'])
                ->where('status', 'belum')
                ->lockForUpdate()
                ->get();

            if ($tagihans->isEmpty()) {
                throw new \Exception('Tidak ada tagihan valid');
            }

            $total = $tagihans->sum('sisa_tagihan');

            if ($total <= 0) {
                throw new \Exception('Total pembayaran tidak valid');
            }

            // 🔹 BUAT 1 PAYMENT
            $payment = Pembayaran::create([
                'tagihan_id' => $tagihans->first()->id, // 🔥 WAJIB
                'amount'  => $total,
                'method'  => $payload['method'],
                'paid_at' => $payload['paid_at'] ?? now(),
                'note'    => $payload['note'] ?? 'Pembayaran kolektif',
                'user_id' => auth()->id(),
            ]);

            // 🔹 UPDATE SEMUA TAGIHAN
            foreach ($tagihans as $tagihan) {
                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'amount'     => $tagihan->sisa_tagihan,
                    'paid_at'    => $payload['paid_at'] ?? now(),
                    'method'     => $payload['method'],
                    'user_id'    => auth()->id(),
                ]);

                $tagihan->update([
                    'status'    => 'lunas',
                    'tgl_bayar' => now(),
                ]);
            }

            return $payment;
        });
    }





    public function delete(Pembayaran $payment): void
    {
        ($this->delete)($payment);
    }
}
