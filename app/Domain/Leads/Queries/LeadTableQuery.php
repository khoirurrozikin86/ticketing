<?php
// app/Domain/Leads/Queries/LeadTableQuery.php
namespace App\Domain\Leads\Queries;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;

class LeadTableQuery
{
    public function builder(): Builder
    {
        return Lead::query()->select([
            'id',
            'name',
            'email',
            'company',
            'message',
            'status',
            'updated_at',
            'created_at'
        ]);
    }
}
