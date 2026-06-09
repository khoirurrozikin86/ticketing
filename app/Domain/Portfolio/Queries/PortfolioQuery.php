<?php

namespace App\Domain\Portfolio\Queries;

use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Builder;

class PortfolioQuery
{
    public function builder(): Builder
    {
        return PortfolioItem::query()->select([
            'id',
            'title',
            'slug',
            'summary',
            'thumb_path',
            'tags',
            'order',
            'created_at',
        ]);
    }
}
