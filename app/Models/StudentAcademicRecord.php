<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAcademicRecord extends Model
{
    protected $fillable = [
        'academic_session_id', 'user_id', 'summary', 'final_average', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'final_average' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
