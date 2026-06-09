<?php

namespace App\Domain\Pembayarans\Actions;

use App\Domain\Pembayarans\DTOs\PaymentData;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreatePaymentAction
{
    public function __invoke(PaymentData $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {
            /** @var Tagihan $tagihan */
            $tagihan = Tagihan::lockForUpdate()->findOrFail($data->tagihan_id);

            // ❌ hard stop kalau sudah lunas
            if ($tagihan->status === 'lunas') {
                throw new RuntimeException('Tagihan sudah lunas dan tidak dapat dibayar lagi.');
            }

            $sudahBayar = (float) $tagihan->pembayarans()->sum('amount');
            $sisa       = max(0, (float)$tagihan->jumlah_tagihan - $sudahBayar);

            // ❌ hindari overpay
            if ($data->amount > $sisa) {
                throw new RuntimeException('Nominal melebihi sisa tagihan (Rp ' . number_format($sisa, 0, ',', '.') . ').');
            }

            // simpan pembayaran
            $pay = Pembayaran::create($data->toArray());

            // hitung ulang & update status
            $totalBayar = (float) $tagihan->pembayarans()->sum('amount');
            if ($totalBayar + 0.00001 >= (float) $tagihan->jumlah_tagihan) {
                $tagihan->update([
                    'status'    => 'lunas',
                    'tgl_bayar' => $tagihan->tgl_bayar ?: $data->paid_at,
                ]);
            } elseif ($tagihan->status !== 'belum') {
                $tagihan->update(['status' => 'belum']);
            }

            return $pay;
        });
    }
}
