<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringReport extends Model
{
    protected $fillable = [
        'mentor_assignment_id', 'chapter_id', 'report_kind', 'author_id', 'body', 'submitted_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function mentorAssignment(): BelongsTo
    {
        return $this->belongsTo(MentorAssignment::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
