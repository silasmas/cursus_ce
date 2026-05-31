<?php

namespace App\Enums;

/**
 * Types de questions dans un test (QCM ou réponse rédigée).
 */
enum QuestionType: string
{
  case Mcq = 'mcq';
  case Written = 'written';

  /**
   * Libellé français affiché dans l'administration.
   */
  public function label(): string
  {
    return match ($this) {
      self::Mcq => 'Question à choix multiples',
      self::Written => 'Réponse rédigée',
    };
  }

  /**
   * Options pour les listes déroulantes Filament.
   *
   * @return array<string, string>
   */
  public static function options(): array
  {
    $options = [];

    foreach (self::cases() as $case) {
      $options[$case->value] = $case->label();
    }

    return $options;
  }

  /**
   * Libellé à partir d'une valeur brute (mcq, written…).
   */
  public static function labelFor(?string $value): string
  {
    if ($value === null) {
      return '—';
    }

    return self::tryFrom($value)?->label() ?? $value;
  }
}
