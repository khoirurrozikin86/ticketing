<?php
// app/Domain/Pages/Actions/DeletePageAction.php
namespace App\Domain\Pages\Actions;

use App\Models\Page;

class DeletePageAction
{
    public function __invoke(Page $page): void
    {
        $page->delete();
    }
}
