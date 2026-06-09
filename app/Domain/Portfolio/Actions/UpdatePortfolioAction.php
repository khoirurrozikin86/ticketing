<?php

namespace App\Domain\Portfolio\Actions;

use App\Domain\Portfolio\DTOs\PortfolioData;
use App\Domain\Portfolio\Services\PortfolioService;
use App\Models\PortfolioItem;

class UpdatePortfolioAction
{
    public function __construct(protected PortfolioService $svc) {}

    public function handle(PortfolioItem $item, PortfolioData $dto): PortfolioItem
    {
        return $this->svc->update($item, $dto);
    }
}
