<?php

namespace App\Domain\Pembayarans\Queries;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentTableQuery
{
    public function builder(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $q = Pembayaran::query()
            ->leftJoin('tagihans',     'tagihans.id',              '=', 'pembayarans.tagihan_id')
            ->leftJoin('pelanggans',   'pelanggans.id_pelanggan',  '=', 'tagihans.id_pelanggan')
            ->leftJoin('users',        'users.id',                 '=', 'pembayarans.user_id')
            ->leftJoin('servers',      'servers.id',               '=', 'pelanggans.id_server')
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

                // ===== tambahkan alias server agar mudah dipakai =====
                'pelanggans.id_server as server_id',
                'servers.lokasi as lokasi_server',   // dipakai di tabel
            ]);

        // Filter lokasi untuk user biasa
        if ($user && ! $user->can('tagihans.view-all') && ! $user->hasRole('super_admin')) {
            if ($user->server_id) {
                $q->where('pelanggans.id_server', $user->server_id);
            } else {
                $q->whereRaw('1=0');
            }
        }

        return $q;
    }

    /** Terapkan filter dari request, termasuk filter server */
    public function applyFilters(Builder $q, array $f): Builder
    {
        // Normalisasi tanggal (inklusif hari penuh)
        $from = !empty($f['date_from']) ? $f['date_from'] : null;
        $to   = !empty($f['date_to'])   ? $f['date_to']   : null;

        if ($from && strlen($from) === 10) $from .= ' 00:00:00';
        if ($to   && strlen($to)   === 10) $to   .= ' 23:59:59';

        return $q
            ->when($from && $to, fn($qq) => $qq->whereBetween('pembayarans.paid_at', [$from, $to]))
            ->when($from && !$to, fn($qq) => $qq->where('pembayarans.paid_at', '>=', $from))
            ->when(!$from && $to, fn($qq) => $qq->where('pembayarans.paid_at', '<=', $to))

            // ==== FILTER SERVER ====
            // single server_id
            ->when(!empty($f['server_id']), fn($qq) => $qq->where('pelanggans.id_server', $f['server_id']))
            // multiple server_ids[]
            ->when(!empty($f['server_ids']) && is_array($f['server_ids']), fn($qq) => $qq->whereIn('pelanggans.id_server', $f['server_ids']))
            // keyword server (nama/lokasi)
            ->when(!empty($f['server_kw']), function ($qq) use ($f) {
                $like = '%' . $f['server_kw'] . '%';
                $qq->where(function ($b) use ($like) {
                    $b->where('servers.nama', 'like', $like)
                        ->orWhere('servers.lokasi', 'like', $like);
                });
            })

            // ==== FILTER LAIN ====
            ->when(!empty($f['user_id']), fn($qq) => $qq->where('pembayarans.user_id', $f['user_id']))
            ->when(!empty($f['kw']), function ($qq) use ($f) {
                $like = '%' . $f['kw'] . '%';
                $qq->where(function ($b) use ($like) {
                    $b->where('tagihans.no_tagihan', 'like', $like)
                        ->orWhere('tagihans.id_pelanggan', 'like', $like)
                        ->orWhere('pelanggans.nama', 'like', $like)
                        ->orWhere('pembayarans.method', 'like', $like)
                        ->orWhere('pembayarans.ref_no', 'like', $like)
                        ->orWhere('servers.lokasi', 'like', $like);   // biar keyword umum juga nyantol ke lokasi

                });
            });
    }
}
