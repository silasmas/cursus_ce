<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Defense extends Model
{
    protected $fillable = [
        'academic_session_id', 'student_user_id', 'scheduled_at', 'mode', 'grade', 'comments', 'jury_user_ids',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'grade' => 'decimal:2',
            'jury_user_ids' => 'array',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}
