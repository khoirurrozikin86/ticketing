<?php

namespace App\Domain\Pakets\Actions;

use App\Domain\Pakets\DTOs\PaketData;
use App\Models\Paket;
use Illuminate\Support\Facades\DB;

class UpdatePaketAction
{
    public function __invoke(Paket $paket, PaketData|array $data): Paket
    {
        $payload = $data instanceof PaketData ? $data->toArray() : $data;

        return DB::transaction(function () use ($paket, $payload) {
            $paket->update($payload);
            return $paket->refresh();
        });
    }
}
