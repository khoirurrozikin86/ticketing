<?php

namespace App\Domain\Pembayarans\Actions;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class DeletePaymentAction
{
    public function __invoke(Pembayaran $payment): void
    {
        DB::transaction(function () use ($payment) {
            $tagihan = $payment->tagihan()->lockForUpdate()->first();

            $payment->delete();

            if ($tagihan) {
                $totalBayar = (float) $tagihan->pembayarans()->sum('amount');

                if ($totalBayar + 0.00001 >= (float) $tagihan->jumlah_tagihan) {
                    // tetap lunas (jarang terjadi setelah delete, kecuali ada pembayaran lain cukup besar)
                    if ($tagihan->status !== 'lunas') {
                        $tagihan->update(['status' => 'lunas', 'tgl_bayar' => $tagihan->tgl_bayar ?: now()]);
                    }
                } else {
                    // jadi belum lunas → kembalikan status & kosongkan tgl_bayar
                    $tagihan->update([
                        'status'    => 'belum',
                        'tgl_bayar' => null,
                    ]);
                }
            }
        });
    }
}
