<?php
// app/Domain/Leads/Actions/UpdateLeadAction.php
namespace App\Domain\Leads\Actions;

use App\Domain\Leads\DTOs\LeadData;
use App\Models\Lead;

class UpdateLeadAction
{
    public function __invoke(Lead $lead, LeadData $data): Lead
    {
        $lead->update([
            'name'    => $data->name,
            'email'   => $data->email,
            'company' => $data->company,
            'message' => $data->message,
            'status'  => $data->status,
        ]);
        return $lead;
    }
}
