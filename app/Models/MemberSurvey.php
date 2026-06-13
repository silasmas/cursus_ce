<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sondage de satisfaction fidèle (une entrée par utilisateur).
 */
class MemberSurvey extends Model
{
  protected $fillable = [
    'user_id',
    'satisfaction',
    'nps_score',
    'comment',
    'weeks_since_enrollment',
    'submitted_at',
    'snoozed_until',
  ];

  /**
   * Casts des attributs datetime et entiers.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'submitted_at' => 'datetime',
      'snoozed_until' => 'datetime',
    ];
  }

  /**
   * Fidèle ayant répondu ou reporté le sondage.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
