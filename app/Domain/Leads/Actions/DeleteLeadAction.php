<?php
// app/Domain/Leads/Actions/DeleteLeadAction.php
namespace App\Domain\Leads\Actions;

use App\Models\Lead;

class DeleteLeadAction
{
    public function __invoke(Lead $lead): void
    {
        $lead->delete();
    }
}
