<?php

namespace App\Services\Mentor;

use App\Models\MentorSetting;

/**
 * Résout les paramètres globaux du module mentorat.
 */
class MentorSettingService
{
  /**
   * Retourne l'enregistrement unique des paramètres mentorat.
   */
  public function current(): MentorSetting
  {
    return MentorSetting::query()->firstOrCreate([], [
      'visible_channels' => ['whatsapp', 'zoom', 'google_meet'],
      'zoom_auto_create_link' => false,
      'google_meet_auto_create_link' => false,
      'notify_with_email' => true,
      'notify_with_sound' => true,
      'notify_with_blink' => true,
      'google_meet_help' => 'Collez un lien Meet existant ou créez-le depuis Google Calendar.',
      'whatsapp_help' => 'Le mentor renseigne manuellement le lien WhatsApp.',
    ]);
  }

  /**
   * Préférences de signal visuel/sonore côté frontend.
   *
   * @return array{sound: bool, blink: bool}
   */
  public function frontendNotificationPreferences(): array
  {
    $settings = $this->current();

    return [
      'sound' => (bool) $settings->notify_with_sound,
      'blink' => (bool) $settings->notify_with_blink,
    ];
  }
}

