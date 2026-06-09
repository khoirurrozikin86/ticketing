<?php
// app/Domain/Leads/Services/LeadService.php
namespace App\Domain\Leads\Services;

use App\Domain\Leads\DTOs\LeadData;
use App\Domain\Leads\Actions\{CreateLeadAction, UpdateLeadAction, DeleteLeadAction};
use App\Models\Lead;

class LeadService
{
    public function __construct(
        protected CreateLeadAction $create,
        protected UpdateLeadAction $update,
        protected DeleteLeadAction $delete,
    ) {}

    public function create(array $payload): Lead
    {
        return ($this->create)(LeadData::fromArray($payload));
    }

    public function update(Lead $lead, array $payload): Lead
    {
        return ($this->update)($lead, LeadData::fromArray($payload));
    }

    public function delete(Lead $lead): void
    {
        ($this->delete)($lead);
    }
}
