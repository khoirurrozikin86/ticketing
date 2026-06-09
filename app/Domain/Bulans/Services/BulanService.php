<?php

namespace App\Domain\Bulans\Services;

use App\Domain\Bulans\DTOs\BulanData;
use App\Domain\Bulans\Actions\{CreateBulanAction, UpdateBulanAction, DeleteBulanAction};
use App\Models\Bulan;

class BulanService
{
    public function __construct(
        protected CreateBulanAction $create,
        protected UpdateBulanAction $update,
        protected DeleteBulanAction $delete,
    ) {}

    public function create(array $payload): Bulan
    {
        return ($this->create)(BulanData::fromArray($payload));
    }

    public function update(Bulan $bulan, array $payload): Bulan
    {
        return ($this->update)($bulan, BulanData::fromArray($payload));
    }

    public function delete(Bulan $bulan): void
    {
        ($this->delete)($bulan);
    }
}
