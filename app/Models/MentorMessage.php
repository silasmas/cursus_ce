<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message échangé entre mentor et mentoré dans le cadre d'une assignation.
 */
class MentorMessage extends Model
{
  protected $fillable = [
    'mentor_assignment_id',
    'sender_id',
    'body',
    'read_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'read_at' => 'datetime',
    ];
  }

  /**
   * Assignation mentorat liée au message.
   */
  public function mentorAssignment(): BelongsTo
  {
    return $this->belongsTo(MentorAssignment::class);
  }

  /**
   * Auteur du message (mentor ou mentoré).
   */
  public function sender(): BelongsTo
  {
    return $this->belongsTo(User::class, 'sender_id');
  }
}
