<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorProfile extends Model
{
    protected $fillable = [
        'user_id', 'max_mentees', 'is_accepting_assignments', 'notes',
        'bio', 'phone', 'whatsapp', 'avatar_path',
    ];

    protected function casts(): array
    {
        return [
            'max_mentees' => 'integer',
            'is_accepting_assignments' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
