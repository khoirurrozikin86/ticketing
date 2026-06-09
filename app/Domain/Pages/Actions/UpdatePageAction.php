<?php
// app/Domain/Pages/Actions/UpdatePageAction.php
namespace App\Domain\Pages\Actions;

use App\Domain\Pages\DTOs\PageData;
use App\Models\Page;

class UpdatePageAction
{
    public function __invoke(Page $page, PageData $data): Page
    {
        $page->update([
            'title'     => $data->title,
            'slug'      => $data->slug,
            'content'   => $data->content,
            'published' => $data->published,
        ]);
        return $page;
    }
}
