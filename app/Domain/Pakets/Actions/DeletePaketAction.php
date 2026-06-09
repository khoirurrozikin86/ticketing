<?php

namespace App\Domain\Pakets\Actions;

use App\Models\Paket;
use Illuminate\Support\Facades\DB;

class DeletePaketAction
{
    public function __invoke(Paket $paket): void
    {
        DB::transaction(function () use ($paket) {
            $paket->delete();
        });
    }
}
