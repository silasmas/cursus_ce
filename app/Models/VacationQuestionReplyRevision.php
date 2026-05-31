<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historique d'une modification de réponse Q&R ECAP.
 */
class VacationQuestionReplyRevision extends Model
{
  /**
   * @var string
   */
  protected $table = 'vacation_reply_revisions';
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'vacation_question_reply_id',
    'body',
    'edited_by_user_id',
  ];

  /**
   * Réponse parente.
   */
  public function reply(): BelongsTo
  {
    return $this->belongsTo(VacationQuestionReply::class, 'vacation_question_reply_id');
  }

  /**
   * Auteur de la modification.
   */
  public function editor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'edited_by_user_id');
  }
}
