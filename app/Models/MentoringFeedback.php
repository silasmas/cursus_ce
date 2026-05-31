<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringFeedback extends Model
{
    protected $table = 'mentoring_feedbacks';

    protected $fillable = [
        'mentor_assignment_id', 'author_id', 'body', 'visible_to_mentor',
        'rating', 'feedback_type',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_mentor' => 'boolean',
            'rating' => 'integer',
        ];
    }

    public function mentorAssignment(): BelongsTo
    {
        return $this->belongsTo(MentorAssignment::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
