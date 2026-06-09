<?php
// app/Domain/TechStacks/Services/TechStackService.php
namespace App\Domain\TechStacks\Services;

use App\Domain\TechStacks\DTOs\TechStackData;
use App\Domain\TechStacks\Actions\{
    CreateTechStackAction,
    UpdateTechStackAction,
    DeleteTechStackAction
};
use App\Models\TechStack;

class TechStackService
{
    public function __construct(
        protected CreateTechStackAction $create,
        protected UpdateTechStackAction $update,
        protected DeleteTechStackAction $delete,
    ) {}

    public function create(array $payload): TechStack
    {
        return ($this->create)(TechStackData::fromArray($payload));
    }

    public function update(TechStack $tech, array $payload): TechStack
    {
        return ($this->update)($tech, TechStackData::fromArray($payload));
    }

    public function delete(TechStack $tech): void
    {
        ($this->delete)($tech);
    }
}
