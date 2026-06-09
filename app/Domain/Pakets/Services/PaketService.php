<?php

namespace App\Domain\Pakets\Services;

use App\Domain\Pakets\DTOs\PaketData;
use App\Domain\Pakets\Actions\{
    CreatePaketAction,
    UpdatePaketAction,
    DeletePaketAction
};
use App\Models\Paket;

class PaketService
{
    public function __construct(
        protected CreatePaketAction $create,
        protected UpdatePaketAction $update,
        protected DeletePaketAction $delete,
    ) {}

    public function create(array $payload): Paket
    {
        return ($this->create)(PaketData::fromArray($payload));
    }

    public function update(Paket $paket, array $payload): Paket
    {
        return ($this->update)($paket, PaketData::fromArray($payload));
    }

    public function delete(Paket $paket): void
    {
        ($this->delete)($paket);
    }
}
