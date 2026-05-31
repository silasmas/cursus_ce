<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrayerSession extends Model
{
    protected $fillable = [
        'mentor_assignment_id', 'learning_group_id', 'title', 'starts_at', 'ends_at', 'meeting_url', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function mentorAssignment(): BelongsTo
    {
        return $this->belongsTo(MentorAssignment::class);
    }

    public function learningGroup(): BelongsTo
    {
        return $this->belongsTo(LearningGroup::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(PrayerSessionAttendee::class);
    }
}
