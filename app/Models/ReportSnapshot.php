<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSnapshot extends Model
{
    protected $fillable = [
        'scope', 'scope_id', 'period', 'metrics', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
