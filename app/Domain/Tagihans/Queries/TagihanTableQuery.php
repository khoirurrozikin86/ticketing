<?php

namespace App\Domain\Tagihans\Queries;

use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;

class TagihanTableQuery
{
    // app/Domain/Tagihans/Queries/TagihanTableQuery.php
    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return \App\Models\Tagihan::query()
            ->leftJoin('pelanggans', 'pelanggans.id_pelanggan', '=', 'tagihans.id_pelanggan')
            ->leftJoin('bulans', 'bulans.id_bulan', '=', 'tagihans.id_bulan')
            ->leftJoin('servers', 'servers.id', '=', 'pelanggans.id_server') // ⬅️ ganti di sini
            ->select([
                'tagihans.id',
                'tagihans.no_tagihan',
                'tagihans.id_bulan',
                'bulans.bulan as nama_bulan',
                'tagihans.tahun',
                'tagihans.id_pelanggan',
                'pelanggans.nama as nama_pelanggan',
                'pelanggans.id_server',                 // ⬅️ kalau perlu dipakai di tabel
                'servers.lokasi as lokasi_server',
                'tagihans.jumlah_tagihan',
                'tagihans.status',
                'tagihans.tgl_bayar',
                'tagihans.remark1',
                'tagihans.remark2',
                'tagihans.remark3',
                'tagihans.updated_at',
                'tagihans.created_at',
            ])
            // batasi data untuk user biasa
            ->when(!$user->can('tagihans.view-all'), function ($q) use ($user) {
                $q->where('pelanggans.id_server', $user->server_id); // ⬅️ ganti di sini
            });
    }


    public function applyFilters($q, array $f)
    {
        return $q->when(($f['only_unpaid'] ?? null) === '1', fn($qq) => $qq->where('tagihans.status', 'belum'));
    }
}
