<?php

namespace App\Domain\Pelanggans\Queries;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PelangganTableQuery
{
    public function builder(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = Pelanggan::query()
            ->leftJoin('pakets', 'pakets.id', '=', 'pelanggans.id_paket')
            ->leftJoin('servers', 'servers.id', '=', 'pelanggans.id_server')
            ->select([
                'pelanggans.id',
                'pelanggans.id_pelanggan',
                'pelanggans.nama',
                'pelanggans.alamat',
                'pelanggans.no_hp',
                'pelanggans.email',
                'pelanggans.id_paket',
                'pakets.nama as paket_nama',
                'pelanggans.id_server',
                'servers.lokasi as server_lokasi',
                'servers.ip as server_ip',
                'pelanggans.ip_router',
                'pelanggans.ip_parent_router',
                'pelanggans.remark1',
                'pelanggans.remark2',
                'pelanggans.remark3',
                'pelanggans.updated_at',
                'pelanggans.created_at',
            ]);

        // 🔒 Filter lokasi sesuai user login
        if ($user && ! $user->can('tagihans.view-all') && ! $user->hasRole('super_admin')) {
            if ($user->server_id) {
                $query->where('pelanggans.id_server', $user->server_id);
            } else {
                $query->whereRaw('1=0'); // user belum diset lokasi
            }
        }

        return $query;
    }
}
