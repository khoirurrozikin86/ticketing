<?php

namespace App\Domain\Pelanggans\Services;

use App\Domain\Pelanggans\DTOs\PelangganData;
use App\Domain\Pelanggans\Actions\{
    CreatePelangganAction,
    UpdatePelangganAction,
    DeletePelangganAction
};
use App\Models\Pelanggan;

class PelangganService
{
    public function __construct(
        protected CreatePelangganAction $create,
        protected UpdatePelangganAction $update,
        protected DeletePelangganAction $delete,
    ) {}

    public function create(array $payload): Pelanggan
    {
        return ($this->create)(PelangganData::fromArray($payload));
    }

    public function update(Pelanggan $pelanggan, array $payload): Pelanggan
    {
        return ($this->update)($pelanggan, PelangganData::fromArray($payload));
    }

    public function delete(Pelanggan $pelanggan): void
    {
        ($this->delete)($pelanggan);
    }
}
