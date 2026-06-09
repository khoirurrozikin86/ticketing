<?php

namespace App\Domain\Portfolio\Actions;

use App\Domain\Portfolio\DTOs\PortfolioData;
use App\Domain\Portfolio\Services\PortfolioService;
use App\Models\PortfolioItem;

class CreatePortfolioAction
{
    public function __construct(protected PortfolioService $svc) {}

    public function handle(PortfolioData $dto): PortfolioItem
    {
        return $this->svc->create($dto);
    }
}
