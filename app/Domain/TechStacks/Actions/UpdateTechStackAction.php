<?php
// app/Domain/TechStacks/Actions/UpdateTechStackAction.php
namespace App\Domain\TechStacks\Actions;

use App\Domain\TechStacks\DTOs\TechStackData;
use App\Models\TechStack;

class UpdateTechStackAction
{
    public function __invoke(TechStack $tech, TechStackData $data): TechStack
    {
        $tech->update([
            'name'  => $data->name,
            'order' => $data->order,
        ]);
        return $tech;
    }
}
