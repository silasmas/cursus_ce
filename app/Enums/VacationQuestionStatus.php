<?php

namespace App\Enums;

/**
 * Statut d'une question posée aux acteurs de vacation ECAP.
 */
enum VacationQuestionStatus: string
{
  case Pending = 'pending';
  case Answered = 'answered';
  case Closed = 'closed';

  /**
   * Libellé français du statut.
   */
  public function label(): string
  {
    return match ($this) {
      self::Pending => 'En attente',
      self::Answered => 'Répondu',
      self::Closed => 'Clôturé',
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
}
