<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Avis d'un acteur ECAP sur une correction de quiz déjà enregistrée.
 */
class AssessmentGradingComment extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'assessment_attempt_id',
    'user_id',
    'body',
  ];

  /**
   * Tentative de quiz concernée.
   */
  public function attempt(): BelongsTo
  {
    return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
  }

  /**
   * Auteur de l'avis (acteur ECAP).
   */
  public function author(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
