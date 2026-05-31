<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    protected $fillable = [
        'disk', 'path', 'mime_type', 'size_bytes', 'duration_seconds', 'transcode_status',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function contentBlocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class);
    }

    /**
     * URL publique du fichier (images uniquement pour l'aperçu admin).
     */
    public function previewUrl(): ?string
    {
        if (! str_starts_with($this->mime_type ?? '', 'image/')) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
