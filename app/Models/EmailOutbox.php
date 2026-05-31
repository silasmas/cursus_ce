<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOutbox extends Model
{
    protected $table = 'email_outbox';

    protected $fillable = [
        'to_email', 'subject', 'body', 'metadata', 'status', 'sent_at', 'attempts',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'sent_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }
}
