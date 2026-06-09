<?php

namespace App\Domain\Pembayarans\Queries;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;

class PaymentLookupQuery
{
    /** 🔍 Pelanggan + info server (max 10, sesuai lokasi user) */
    public function pelangganBuilder(string $q): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $like = '%' . $q . '%';

        $query = Pelanggan::query()
            ->leftJoin('servers', 'servers.id', '=', 'pelanggans.id_server')
            ->select([
                'pelanggans.id_pelanggan',
                'pelanggans.nama',
                'pelanggans.alamat',
                'pelanggans.no_hp',
                'pelanggans.email',
                'pelanggans.id_server',
                'servers.ip as server_ip',
                'servers.lokasi as server_lokasi',
                'servers.mikrotik as server_mikrotik',
            ])
            ->where(function ($w) use ($like) {
                $w->where('pelanggans.id_pelanggan', 'like', $like)
                    ->orWhere('pelanggans.nama', 'like', $like);
            });

        // 🔒 Batasi berdasarkan lokasi user (server_id)
        if ($user && ! $user->can('tagihans.view-all') && ! $user->hasRole('super_admin')) {
            if ($user->server_id) {
                $query->where('pelanggans.id_server', $user->server_id);
            } else {
                $query->whereRaw('1=0'); // kalau user belum di-set lokasi
            }
        }

        return $query->orderBy('pelanggans.nama')->limit(10);
    }

    /** 📋 Tagihan grouped by id_pelanggan (prioritas ‘belum’) */
    public function tagihansByPelanggan(array $pelangganIds): Collection
    {
        if (empty($pelangganIds)) return collect();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = Tagihan::query()
            ->leftJoin('pelanggans', 'pelanggans.id_pelanggan', '=', 'tagihans.id_pelanggan')
            ->leftJoin('bulans', 'bulans.id_bulan', '=', 'tagihans.id_bulan')
            ->select([
                'tagihans.id',
                'tagihans.no_tagihan',
                'tagihans.id_pelanggan',
                'tagihans.jumlah_tagihan',
                'tagihans.status',
                'tagihans.tgl_bayar',
                'tagihans.tahun',
                'tagihans.id_bulan',
                'bulans.bulan as nama_bulan',
                'tagihans.updated_at',
            ])
            ->whereIn('tagihans.id_pelanggan', $pelangganIds)
            ->orderByRaw("CASE WHEN tagihans.status='belum' THEN 0 ELSE 1 END")
            ->orderByDesc('tagihans.updated_at');

        // 🔒 Filter lokasi (user biasa hanya lihat tagihan server miliknya)
        if ($user && ! $user->can('tagihans.view-all') && ! $user->hasRole('super_admin')) {
            if ($user->server_id) {
                $query->where('pelanggans.id_server', $user->server_id);
            } else {
                $query->whereRaw('1=0');
            }
        }

        return $query->get()->groupBy('id_pelanggan');
    }
}
