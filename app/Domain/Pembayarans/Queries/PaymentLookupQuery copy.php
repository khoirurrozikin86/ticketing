<?php

namespace App\Domain\Pembayarans\Queries;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pelanggan;
use App\Models\Tagihan;

class PaymentLookupQuery
{
    /** Pelanggan + info server (max 10) */
    public function pelangganBuilder(string $q): Builder
    {
        $like = '%' . $q . '%';

        return Pelanggan::query()
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
            })
            ->orderBy('pelanggans.nama')
            ->limit(10);
    }

    /** Tagihan grouped by id_pelanggan (prioritas ‘belum’) */
    public function tagihansByPelanggan(array $pelangganIds): Collection
    {
        if (empty($pelangganIds)) return collect();

        return Tagihan::query()
            ->leftJoin('bulans', 'bulans.id_bulan', '=', 'tagihans.id_bulan')
            ->whereIn('tagihans.id_pelanggan', $pelangganIds)
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
            ->orderByRaw("CASE WHEN tagihans.status='belum' THEN 0 ELSE 1 END")
            ->orderByDesc('tagihans.updated_at')
            ->get()
            ->groupBy('id_pelanggan');
    }
}
