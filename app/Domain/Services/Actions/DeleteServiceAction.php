<?php
// app/Domain/Services/Actions/DeleteServiceAction.php
namespace App\Domain\Services\Actions;

use App\Models\Service;

class DeleteServiceAction
{
    public function __invoke(Service $service): void
    {
        $service->delete();
    }
}
