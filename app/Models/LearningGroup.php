<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningGroup extends Model
{
    protected $fillable = [
        'academic_session_id', 'name', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(LearningGroupMember::class);
    }

    public function prayerSessions(): HasMany
    {
        return $this->hasMany(PrayerSession::class);
    }
}
