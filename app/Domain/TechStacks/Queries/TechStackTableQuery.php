<?php
// app/Domain/TechStacks/Queries/TechStackTableQuery.php
namespace App\Domain\TechStacks\Queries;

use App\Models\TechStack;
use Illuminate\Database\Eloquent\Builder;

class TechStackTableQuery
{
    public function builder(): Builder
    {
        return TechStack::query()
            ->select(['id', 'name', 'order', 'updated_at', 'created_at']);
    }
}
