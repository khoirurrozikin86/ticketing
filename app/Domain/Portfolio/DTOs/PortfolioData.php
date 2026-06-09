<?php

namespace App\Domain\Portfolio\DTOs;

use Illuminate\Http\UploadedFile;

class PortfolioData
{
    public function __construct(
        public string $title,
        public ?string $summary = null,
        public ?string $tags = null,
        public ?UploadedFile $thumb = null,
        public ?int $order = 0,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            summary: $data['summary'] ?? null,
            tags: $data['tags'] ?? null,
            thumb: $data['thumb'] ?? null,
            order: $data['order'] ?? 0,
        );
    }
}
