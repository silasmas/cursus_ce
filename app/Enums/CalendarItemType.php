<?php

namespace App\Enums;

/**
 * Types d'entrées du calendrier ECAP (module ou activité).
 */
enum CalendarItemType: string
{
  case Module = 'module';

  case Activity = 'activity';

  /**
   * Libellé affiché dans l'administration.
   */
  public function label(): string
  {
    return match ($this) {
      self::Module => 'Module de cours',
      self::Activity => 'Activité',
    };
  }
}
