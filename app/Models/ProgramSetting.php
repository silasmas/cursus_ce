<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramSetting extends Model
{
    protected $fillable = [
        'program_id', 'linear_progression', 'quiz_mandatory', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'linear_progression' => 'boolean',
            'quiz_mandatory' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
