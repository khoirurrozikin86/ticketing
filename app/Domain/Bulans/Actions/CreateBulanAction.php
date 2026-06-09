<?php

namespace App\Domain\Bulans\Actions;

use App\Domain\Bulans\DTOs\BulanData;
use App\Models\Bulan;
use Illuminate\Support\Facades\DB;

class CreateBulanAction
{
    public function __invoke(BulanData $data): Bulan
    {
        return DB::transaction(fn() => Bulan::create($data->toArray()));
    }
}
