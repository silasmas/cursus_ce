<?php

namespace App\Enums;

/**
 * Réponse du mentoré à une invitation de rendez-vous.
 */
enum AppointmentMenteeResponse: string
{
  case Pending = 'pending';
  case Accepted = 'accepted';
  case Declined = 'declined';
  case Postponed = 'postponed';

  /**
   * Libellé affiché dans l'interface.
   */
  public function label(): string
  {
    return match ($this) {
      self::Pending => 'En attente de réponse',
      self::Accepted => 'Accepté',
      self::Declined => 'Refusé',
      self::Postponed => 'Reporté',
    };
  }
}
