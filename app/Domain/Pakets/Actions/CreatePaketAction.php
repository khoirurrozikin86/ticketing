<?php

namespace App\Domain\Pakets\Actions;

use App\Domain\Pakets\DTOs\PaketData;
use App\Models\Paket;
use Illuminate\Support\Facades\DB;

class CreatePaketAction
{
    public function __invoke(PaketData|array $data): Paket
    {
        $payload = $data instanceof PaketData ? $data->toArray() : $data;

        return DB::transaction(function () use ($payload) {
            return Paket::create($payload);
        });
    }
}
