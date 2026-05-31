<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterProgress extends Model
{
    protected $table = 'chapter_progress';

    protected $fillable = [
        'enrollment_id', 'chapter_id', 'last_content_block_id', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function lastContentBlock(): BelongsTo
    {
        return $this->belongsTo(ContentBlock::class, 'last_content_block_id');
    }
}
