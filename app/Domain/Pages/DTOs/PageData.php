<?php
// app/Domain/Pages/DTOs/PageData.php
namespace App\Domain\Pages\DTOs;

class PageData
{
    public function __construct(
        public string $title,
        public string $slug,
        public array $content = [],
        public bool $published = true,
    ) {}

    public static function fromArray(array $data): self
    {
        $content = $data['content'] ?? [];
        if (is_string($content) && $content !== '') {
            $decoded = json_decode($content, true);
            $content = is_array($decoded) ? $decoded : [];
        }

        return new self(
            title: $data['title'],
            slug: $data['slug'],
            content: $content,
            published: (bool)($data['published'] ?? true),
        );
    }
}
