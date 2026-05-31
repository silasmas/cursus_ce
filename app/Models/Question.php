<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'assessment_id', 'type', 'stem', 'sort_order', 'points', 'review_chapter_id', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'points' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Chapitre de révision proposé en cas de mauvaise réponse (M5).
     */
    public function reviewChapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'review_chapter_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
