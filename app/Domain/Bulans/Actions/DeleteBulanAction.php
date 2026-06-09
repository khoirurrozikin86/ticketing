<?php

namespace App\Domain\Bulans\Actions;

use App\Models\Bulan;
use Illuminate\Support\Facades\DB;

class DeleteBulanAction
{
    public function __invoke(Bulan $bulan): void
    {
        DB::transaction(fn() => $bulan->delete());
    }
}
