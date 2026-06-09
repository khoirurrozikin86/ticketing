<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PortfolioItem extends Model
{
    protected $fillable = ['title', 'slug', 'summary', 'thumb_path', 'tags', 'order'];
    protected $casts = ['tags' => 'array'];

    // aksesori kecil utk tampilan tag sebagai string
    public function getTagsLineAttribute(): string
    {
        $tags = $this->tags ?? [];
        return implode(', ', array_filter($tags, fn($t) => strlen(trim((string)$t)) > 0));
    }
}
