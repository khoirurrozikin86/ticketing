<?php

namespace App\Domain\Pakets\Queries;

use App\Models\Paket;

class PaketTableQuery
{
    public function builder()
    {
        return Paket::query()->select([
            'id',
            'id_paket',
            'nama',
            'harga',
            'kecepatan',
            'durasi',
            'remark1',
            'remark2',
            'remark3',
            'created_at'
        ]);
    }
}
