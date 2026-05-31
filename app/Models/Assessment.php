<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'program_id', 'course_id', 'chapter_id', 'course_module_id', 'title', 'type',
        'is_module_exit_quiz', 'time_limit_seconds', 'max_attempts', 'passing_score', 'required_questions', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_module_exit_quiz' => 'boolean',
            'max_attempts' => 'integer',
            'time_limit_seconds' => 'integer',
            'passing_score' => 'decimal:2',
            'required_questions' => 'integer',
        ];
    }

    /**
     * Nombre de questions attendu pour ce quiz (null = pas de quota fixe).
     */
    public function questionQuota(): ?int
    {
        return $this->required_questions !== null
            ? (int) $this->required_questions
            : null;
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function courseModule(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
