<?php
// app/Domain/TechStacks/Actions/CreateTechStackAction.php
namespace App\Domain\TechStacks\Actions;

use App\Domain\TechStacks\DTOs\TechStackData;
use App\Models\TechStack;

class CreateTechStackAction
{
    public function __invoke(TechStackData $data): TechStack
    {
        return TechStack::create([
            'name'  => $data->name,
            'order' => $data->order,
        ]);
    }
}
