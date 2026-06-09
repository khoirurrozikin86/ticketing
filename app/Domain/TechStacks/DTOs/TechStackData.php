<?php
// app/Domain/TechStacks/DTOs/TechStackData.php
namespace App\Domain\TechStacks\DTOs;

class TechStackData
{
    public function __construct(
        public string $name,
        public int $order = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            order: (int) ($data['order'] ?? 0),
        );
    }
}
