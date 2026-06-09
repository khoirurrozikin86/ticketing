<?php
// app/Domain/Pages/Queries/PageTableQuery.php
namespace App\Domain\Pages\Queries;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;

class PageTableQuery
{
    public function builder(): Builder
    {
        return Page::query()->select(['id', 'title', 'slug', 'content', 'published', 'updated_at', 'created_at']);
    }
}
