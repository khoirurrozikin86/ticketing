<?php
// app/Domain/Services/DTOs/ServiceData.php
namespace App\Domain\Services\DTOs;

class ServiceData
{
    public function __construct(
        public string $name,
        public ?string $icon = null,
        public ?string $excerpt = null,
        public array $meta = [],
        public int $order = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        $meta = $data['meta'] ?? [];
        if (is_string($meta) && $meta !== '') {
            $meta = json_decode($meta, true) ?? [];
        }

        return new self(
            name: $data['name'],
            icon: $data['icon'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            meta: is_array($meta) ? $meta : [],
            order: (int)($data['order'] ?? 0),
        );
    }
}
