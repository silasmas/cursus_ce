<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paramètres globaux du module mentorat.
 */
class MentorSetting extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'visible_channels',
    'zoom_auto_create_link',
    'google_meet_auto_create_link',
    'notify_with_email',
    'notify_with_sound',
    'notify_with_blink',
    'google_meet_help',
    'whatsapp_help',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'visible_channels' => 'array',
      'zoom_auto_create_link' => 'boolean',
      'google_meet_auto_create_link' => 'boolean',
      'notify_with_email' => 'boolean',
      'notify_with_sound' => 'boolean',
      'notify_with_blink' => 'boolean',
    ];
  }
}

