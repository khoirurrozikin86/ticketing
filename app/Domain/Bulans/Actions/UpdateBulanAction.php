<?php

namespace App\Domain\Bulans\Actions;

use App\Domain\Bulans\DTOs\BulanData;
use App\Models\Bulan;
use Illuminate\Support\Facades\DB;

class UpdateBulanAction
{
    public function __invoke(Bulan $bulan, BulanData $data): Bulan
    {
        return DB::transaction(function () use ($bulan, $data) {
            $bulan->update($data->toArray());
            return $bulan->refresh();
        });
    }
}
