<?php

namespace App\Models;

use App\Enums\PortalNotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification in-app pour fidèles et mentors.
 */
class PortalNotification extends Model
{
  protected $fillable = [
    'user_id',
    'type',
    'title',
    'body',
    'action_url',
    'action_label',
    'metadata',
    'read_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'type' => PortalNotificationType::class,
      'metadata' => 'array',
      'read_at' => 'datetime',
    ];
  }

  /**
   * Destinataire de la notification.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Indique si la notification a été lue.
   */
  public function isRead(): bool
  {
    return $this->read_at !== null;
  }
}
