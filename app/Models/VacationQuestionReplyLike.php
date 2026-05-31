<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Like (pouce) d'un fidèle sur une réponse du fil ECAP.
 */
class VacationQuestionReplyLike extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'vacation_question_reply_id',
    'user_id',
  ];

  /**
   * Réponse likée.
   */
  public function reply(): BelongsTo
  {
    return $this->belongsTo(VacationQuestionReply::class, 'vacation_question_reply_id');
  }

  /**
   * Utilisateur ayant mis le pouce.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
