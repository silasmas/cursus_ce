<?php

namespace App\Enums;

/**
 * Types de périodes dans une génération ECAP.
 */
enum SessionPeriodType: string
{
  case Courses = 'courses';

  case Tfe = 'tfe';

  case Defenses = 'defenses';

  /**
   * Libellé affiché dans l'administration.
   */
  public function label(): string
  {
    return match ($this) {
      self::Courses => 'Période des cours',
      self::Tfe => 'Travaux de fin d\'études',
      self::Defenses => 'Défenses',
    };
  }
}
