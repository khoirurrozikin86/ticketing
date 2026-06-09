<?php
// app/Domain/Services/Actions/CreateServiceAction.php
namespace App\Domain\Services\Actions;

use App\Domain\Services\DTOs\ServiceData;
use App\Models\Service;

class CreateServiceAction
{
    public function __invoke(ServiceData $data): Service
    {
        return Service::create([
            'name'    => $data->name,
            'icon'    => $data->icon,
            'excerpt' => $data->excerpt,
            'meta'    => $data->meta,
            'order'   => $data->order,
        ]);
    }
}
