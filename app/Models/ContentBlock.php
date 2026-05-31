<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBlock extends Model
{
    protected $fillable = [
        'chapter_id', 'type', 'sort_order', 'title', 'body', 'media_asset_id', 'url', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function contentBlockProgress(): HasMany
    {
        return $this->hasMany(ContentBlockProgress::class);
    }
}
