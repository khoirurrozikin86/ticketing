<?php

namespace App\Domain\Portfolio\Actions;

use App\Domain\Portfolio\Services\PortfolioService;
use App\Models\PortfolioItem;

class DeletePortfolioAction
{
    public function __construct(protected PortfolioService $svc) {}

    public function handle(PortfolioItem $item): void
    {
        $this->svc->delete($item);
    }
}
