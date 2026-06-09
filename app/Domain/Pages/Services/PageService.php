<?php
// app/Domain/Pages/Services/PageService.php
namespace App\Domain\Pages\Services;

use App\Domain\Pages\DTOs\PageData;
use App\Domain\Pages\Actions\{CreatePageAction, UpdatePageAction, DeletePageAction};
use App\Models\Page;

class PageService
{
    public function __construct(
        protected CreatePageAction $create,
        protected UpdatePageAction $update,
        protected DeletePageAction $delete,
    ) {}

    public function create(array $payload): Page
    {
        return ($this->create)(PageData::fromArray($payload));
    }

    public function update(Page $page, array $payload): Page
    {
        return ($this->update)($page, PageData::fromArray($payload));
    }

    public function delete(Page $page): void
    {
        ($this->delete)($page);
    }
}
