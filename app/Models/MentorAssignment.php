<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorAssignment extends Model
{
    protected $fillable = [
        'mentor_id', 'mentee_id', 'program_id', 'enrollment_id', 'assigned_by_user_id',
        'assignment_mode', 'status', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MentoringReport::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(MentoringFeedback::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(MentoringDecision::class);
    }

    public function prayerSessions(): HasMany
    {
        return $this->hasMany(PrayerSession::class);
    }

    /**
     * Messages échangés entre mentor et mentoré.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(MentorMessage::class);
    }

    /**
     * Rendez-vous programmés pour cette assignation.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(MentorAppointment::class);
    }
}
