<?php

namespace App\Domain\Pembayarans\Queries;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;

class PaymentTableQueryc
{
    public function builder(): Builder
    {
        return Pembayaran::query()
            ->leftJoin('tagihans', 'tagihans.id', '=', 'pembayarans.tagihan_id')
            ->leftJoin('pelanggans', 'pelanggans.id_pelanggan', '=', 'tagihans.id_pelanggan')
            ->leftJoin('users', 'users.id', '=', 'pembayarans.user_id')
            ->select([
                'pembayarans.id',
                'pembayarans.tagihan_id',
                'pembayarans.amount',
                'pembayarans.paid_at',
                'pembayarans.method',
                'pembayarans.ref_no',
                'pembayarans.note',
                'pembayarans.user_id',
                'pembayarans.created_at',
                'pembayarans.updated_at',
                'tagihans.no_tagihan',
                'tagihans.id_pelanggan',
                'pelanggans.nama as nama_pelanggan',
                'users.name as user_name',
            ]);
    }

    /** Terapkan filter dari request */
    public function applyFilters(Builder $q, array $f): Builder
    {
        return $q
            ->when(!empty($f['date_from']) && !empty($f['date_to']), function ($qq) use ($f) {
                $qq->whereBetween('pembayarans.paid_at', [$f['date_from'], $f['date_to']]);
            })
            ->when(!empty($f['user_id']), fn($qq) => $qq->where('pembayarans.user_id', $f['user_id']))
            ->when(!empty($f['kw']), function ($qq) use ($f) {
                $like = '%' . $f['kw'] . '%';
                $qq->where(function ($b) use ($like) {
                    $b->where('tagihans.no_tagihan', 'like', $like)
                        ->orWhere('tagihans.id_pelanggan', 'like', $like)
                        ->orWhere('pelanggans.nama', 'like', $like)
                        ->orWhere('pembayarans.method', 'like', $like)
                        ->orWhere('pembayarans.ref_no', 'like', $like);
                });
            });
    }
}
