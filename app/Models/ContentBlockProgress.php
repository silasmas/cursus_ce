<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBlockProgress extends Model
{
    protected $table = 'content_block_progress';

    protected $fillable = [
        'enrollment_id', 'content_block_id', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(ContentBlock::class);
    }
}
