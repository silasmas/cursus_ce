<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message privé entre un fidèle ECAP et un acteur (superviseur, modérateur).
 */
class EcapDirectMessage extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'academic_session_id',
    'sender_user_id',
    'recipient_user_id',
    'subject_context',
    'subject_id',
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
   * Session ECAP concernée.
   */
  public function academicSession(): BelongsTo
  {
    return $this->belongsTo(AcademicSession::class);
  }

  /**
   * Expéditeur du message.
   */
  public function sender(): BelongsTo
  {
    return $this->belongsTo(User::class, 'sender_user_id');
  }

  /**
   * Destinataire du message.
   */
  public function recipient(): BelongsTo
  {
    return $this->belongsTo(User::class, 'recipient_user_id');
  }
}
