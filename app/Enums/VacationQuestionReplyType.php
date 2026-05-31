<?php

namespace App\Enums;

/**
 * Type de contribution sur une réponse Q&R ECAP.
 */
enum VacationQuestionReplyType: string
{
  case Answer = 'answer';
  case Comment = 'comment';

  /**
   * Libellé affiché côté portail.
   */
  public function label(): string
  {
    return match ($this) {
      self::Answer => 'Réponse',
      self::Comment => 'Avis',
    };
  }
}
