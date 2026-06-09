<?php

namespace App\Domain\Portfolio\Services;

use App\Domain\Portfolio\DTOs\PortfolioData;
use App\Models\PortfolioItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioService
{
    /** Generate slug unik */
    public function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $i = 1;
        while (PortfolioItem::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /** Upload thumbnail baru dan hapus lama */
    public function handleThumbUpload(?\Illuminate\Http\UploadedFile $file, ?string $oldPath = null): ?string
    {
        if (!$file) return $oldPath;
        if ($oldPath) Storage::disk('public')->delete($oldPath);
        return $file->store('portfolios', 'public');
    }

    /** Convert string tags jadi array */
    public function normalizeTags(?string $raw): array
    {
        if (!$raw) return [];
        $arr = array_map(fn($t) => trim(strip_tags($t)), explode(',', $raw));
        return array_values(array_filter($arr, fn($v) => $v !== ''));
    }

    /** Create */
    public function create(PortfolioData $dto): PortfolioItem
    {
        $slug = $this->generateUniqueSlug($dto->title);
        $thumb = $this->handleThumbUpload($dto->thumb);
        $tags = $this->normalizeTags($dto->tags);

        return PortfolioItem::create([
            'title' => $dto->title,
            'slug' => $slug,
            'summary' => $dto->summary,
            'thumb_path' => $thumb,
            'tags' => $tags,
            'order' => $dto->order,
        ]);
    }

    /** Update */
    public function update(PortfolioItem $item, PortfolioData $dto): PortfolioItem
    {
        $slug = $this->generateUniqueSlug($dto->title, $item->id);
        $thumb = $this->handleThumbUpload($dto->thumb, $item->thumb_path);
        $tags = $this->normalizeTags($dto->tags);

        $item->update([
            'title' => $dto->title,
            'slug' => $slug,
            'summary' => $dto->summary,
            'thumb_path' => $thumb,
            'tags' => $tags,
            'order' => $dto->order,
        ]);
        return $item;
    }

    /** Delete + remove file */
    public function delete(PortfolioItem $item): void
    {
        if ($item->thumb_path) Storage::disk('public')->delete($item->thumb_path);
        $item->delete();
    }
}
