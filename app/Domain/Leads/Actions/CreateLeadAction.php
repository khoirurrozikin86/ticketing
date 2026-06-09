<?php
// app/Domain/Leads/Actions/CreateLeadAction.php
namespace App\Domain\Leads\Actions;

use App\Domain\Leads\DTOs\LeadData;
use App\Models\Lead;

class CreateLeadAction
{
    public function __invoke(LeadData $data): Lead
    {
        return Lead::create([
            'name'    => $data->name,
            'email'   => $data->email,
            'company' => $data->company,
            'message' => $data->message,
            'status'  => $data->status,
        ]);
    }
}
