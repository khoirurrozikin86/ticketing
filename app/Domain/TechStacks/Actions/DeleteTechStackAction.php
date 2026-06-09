<?php
// app/Domain/TechStacks/Actions/DeleteTechStackAction.php
namespace App\Domain\TechStacks\Actions;

use App\Models\TechStack;

class DeleteTechStackAction
{
    public function __invoke(TechStack $tech): void
    {
        $tech->delete();
    }
}
