<?php

namespace App\Models;

use App\Enums\VacationQuestionReplyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Réponse ou avis d'un acteur ECAP sur une question (fil de discussion).
 */
class VacationQuestionReply extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'vacation_question_id',
    'user_id',
    'body',
    'reply_type',
    'parent_reply_id',
    'edited_at',
    'version',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'reply_type' => VacationQuestionReplyType::class,
      'edited_at' => 'datetime',
      'version' => 'integer',
    ];
  }

  /**
   * Question parente.
   */
  public function question(): BelongsTo
  {
    return $this->belongsTo(VacationQuestion::class, 'vacation_question_id');
  }

  /**
   * Auteur de la réponse (enseignant ou autre acteur).
   */
  public function author(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Réponse officielle parente (pour un avis).
   */
  public function parentReply(): BelongsTo
  {
    return $this->belongsTo(self::class, 'parent_reply_id');
  }

  /**
   * Avis des autres acteurs sur cette réponse.
   *
   * @return HasMany<VacationQuestionReply>
   */
  public function comments(): HasMany
  {
    return $this->hasMany(self::class, 'parent_reply_id')->orderBy('created_at');
  }

  /**
   * Pouce « utile » des fidèles.
   */
  public function likes(): HasMany
  {
    return $this->hasMany(VacationQuestionReplyLike::class);
  }

  /**
   * Versions précédentes après édition.
   *
   * @return HasMany<VacationQuestionReplyRevision>
   */
  public function revisions(): HasMany
  {
    return $this->hasMany(VacationQuestionReplyRevision::class)->orderByDesc('created_at');
  }
}
