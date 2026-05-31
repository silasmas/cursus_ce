<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assessment_id', 'user_id', 'submitted_by_user_id', 'enrollment_id', 'version', 'file_path',
        'answer_text', 'submitted_at', 'visible_to_mentee', 'admin_publication_status',
        'status', 'mentor_status', 'mentor_notes',
        'mentor_reviewer_id', 'mentor_reviewed_at', 'grade', 'grader_notes',
        'grader_id', 'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'mentor_reviewed_at' => 'datetime',
            'grade' => 'decimal:2',
            'visible_to_mentee' => 'boolean',
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

    /**
     * Formateur ou admin ayant validé le TP.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grader_id');
    }

    /**
     * Mentor ayant validé ou refusé la remise.
     */
    public function mentorReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_reviewer_id');
    }

    /**
     * Utilisateur ayant soumis le TP (mentor ou mentoré).
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * Indique si la remise est visible côté mentoré.
     */
    public function isVisibleToMentee(): bool
    {
        return $this->visible_to_mentee
            && $this->admin_publication_status === 'published';
    }

    /**
     * Indique si un fichier est joint à la remise.
     */
    public function hasAttachedFile(): bool
    {
        return filled($this->file_path);
    }

    /**
     * URL publique du fichier joint, si présent.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (! $this->hasAttachedFile()) {
            return null;
        }

        return asset('storage/'.$this->file_path);
    }
}
