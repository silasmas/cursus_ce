<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptAnswer extends Model
{
    protected $fillable = [
        'assessment_attempt_id', 'question_id', 'answer_text', 'question_option_id', 'file_path', 'points_awarded', 'grader_feedback',
    ];

    protected function casts(): array
    {
        return ['points_awarded' => 'decimal:2'];
    }

    public function assessmentAttempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function questionOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
