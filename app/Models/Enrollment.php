<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'program_id',
        'course_id',
        'academic_session_id',
        'is_online',
        'session_vacation_id',
        'online_mode_updated_at',
        'online_mode_updated_by_user_id',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'online_mode_updated_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /**
     * Vacation présentiel (inscription ECAP).
     */
    public function sessionVacation(): BelongsTo
    {
        return $this->belongsTo(SessionVacation::class);
    }

    public function chapterProgress(): HasMany
    {
        return $this->hasMany(ChapterProgress::class);
    }

    public function contentBlockProgress(): HasMany
    {
        return $this->hasMany(ContentBlockProgress::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
