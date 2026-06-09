<?php

// app/Domain/Services/Queries/ServiceTableQuery.php
namespace App\Domain\Services\Queries;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class ServiceTableQuery
{
    public function builder(): Builder
    {
        return Service::query()->select([
            'id',
            'name',
            'icon',
            'excerpt',
            'meta',
            'order',
            'updated_at',
            'created_at'
        ]);
    }
}
