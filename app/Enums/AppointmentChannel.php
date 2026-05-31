<?php

namespace App\Enums;

/**
 * Canaux de rendez-vous en ligne mentor / mentoré.
 */
enum AppointmentChannel: string
{
  case Whatsapp = 'whatsapp';
  case Zoom = 'zoom';
  case GoogleMeet = 'google_meet';

  /**
   * Libellé affiché dans l'interface.
   */
  public function label(): string
  {
    return match ($this) {
      self::Whatsapp => 'WhatsApp',
      self::Zoom => 'Zoom',
      self::GoogleMeet => 'Google Meet',
    };
  }
}
