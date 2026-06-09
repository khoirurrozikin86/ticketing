<?php
// app/Domain/Services/Actions/UpdateServiceAction.php
namespace App\Domain\Services\Actions;

use App\Domain\Services\DTOs\ServiceData;
use App\Models\Service;

class UpdateServiceAction
{
    public function __invoke(Service $service, ServiceData $data): Service
    {
        $service->update([
            'name'    => $data->name,
            'icon'    => $data->icon,
            'excerpt' => $data->excerpt,
            'meta'    => $data->meta,
            'order'   => $data->order,
        ]);

        return $service;
    }
}
