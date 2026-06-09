<?php
// app/Domain/Pages/Actions/CreatePageAction.php
namespace App\Domain\Pages\Actions;

use App\Domain\Pages\DTOs\PageData;
use App\Models\Page;

class CreatePageAction
{
    public function __invoke(PageData $data): Page
    {
        return Page::create([
            'title'     => $data->title,
            'slug'      => $data->slug,
            'content'   => $data->content,
            'published' => $data->published,
        ]);
    }
}
