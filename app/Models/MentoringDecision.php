<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringDecision extends Model
{
    protected $fillable = [
        'mentor_assignment_id', 'chapter_id', 'assignment_submission_id',
        'decided_by_user_id', 'decision', 'notes', 'decided_at',
    ];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    public function mentorAssignment(): BelongsTo
    {
        return $this->belongsTo(MentorAssignment::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
