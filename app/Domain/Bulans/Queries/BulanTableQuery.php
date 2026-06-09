<?php

namespace App\Domain\Bulans\Queries;

use App\Models\Bulan;
use Illuminate\Database\Eloquent\Builder;

class BulanTableQuery
{
    public function builder(): Builder
    {
        return Bulan::query()->select(['id_bulan', 'bulan', 'updated_at', 'created_at']);
    }
}
