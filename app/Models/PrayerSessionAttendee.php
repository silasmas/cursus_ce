<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrayerSessionAttendee extends Model
{
    protected $fillable = [
        'prayer_session_id', 'user_id', 'attended',
    ];

    protected function casts(): array
    {
        return ['attended' => 'boolean'];
    }

    public function prayerSession(): BelongsTo
    {
        return $this->belongsTo(PrayerSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
