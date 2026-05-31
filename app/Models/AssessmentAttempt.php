<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    protected $fillable = [
        'assessment_id', 'user_id', 'enrollment_id', 'started_at', 'submitted_at', 'score', 'passed', 'status',
        'grading_locked_by_user_id', 'grading_locked_at', 'graded_by_user_id', 'grading_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'grading_locked_at' => 'datetime',
            'grading_notified_at' => 'datetime',
            'passed' => 'boolean',
            'score' => 'decimal:2',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    /**
     * Acteur qui corrige actuellement cette tentative.
     */
    public function gradingLockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grading_locked_by_user_id');
    }

    /**
     * Dernier correcteur ayant finalisé la tentative.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }

    /**
     * Avis des autres acteurs ECAP après correction.
     *
     * @return HasMany<AssessmentGradingComment>
     */
    public function gradingComments(): HasMany
    {
        return $this->hasMany(AssessmentGradingComment::class)->orderBy('created_at');
    }
}
