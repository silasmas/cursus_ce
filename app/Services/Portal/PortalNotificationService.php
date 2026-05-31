<?php

namespace App\Services\Portal;

use App\Enums\PortalNotificationType;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Crée et gère les notifications in-app du portail.
 */
class PortalNotificationService
{
  /**
   * @param  PortalMailService  $mailService  Envoi e-mail
   */
  public function __construct(
    private readonly PortalMailService $mailService,
  ) {}

  /**
   * Envoie une notification in-app et un e-mail à un utilisateur.
   */
  public function notifyWithEmail(
    User $user,
    PortalNotificationType $type,
    string $title,
    string $body,
    ?string $actionUrl = null,
    ?string $actionLabel = null,
    ?array $metadata = null,
    ?string $emailSubject = null,
  ): PortalNotification {
    $notification = $this->notify(
      $user,
      $type,
      $title,
      $body,
      $actionUrl,
      $actionLabel,
      $metadata,
    );

    $this->mailService->sendToUser(
      $user,
      $emailSubject ?? $title,
      $title,
      $body,
      $actionUrl,
      $actionLabel,
    );

    return $notification;
  }

  /**
   * Envoie une notification à un utilisateur.
   */
  public function notify(
    User $user,
    PortalNotificationType $type,
    string $title,
    string $body,
    ?string $actionUrl = null,
    ?string $actionLabel = null,
    ?array $metadata = null,
  ): PortalNotification {
    return PortalNotification::query()->create([
      'user_id' => $user->id,
      'type' => $type->value,
      'title' => $title,
      'body' => $body,
      'action_url' => $actionUrl,
      'action_label' => $actionLabel,
      'metadata' => $metadata,
    ]);
  }

  /**
   * Notifications récentes pour l'affichage cloche.
   *
   * @return array<int, array<string, mixed>>
   */
  public function recentForUser(User $user, int $limit = 12): array
  {
    return PortalNotification::query()
      ->where('user_id', $user->id)
      ->latest()
      ->limit($limit)
      ->get()
      ->map(fn (PortalNotification $n) => $this->payload($n))
      ->all();
  }

  /**
   * Nombre de notifications non lues.
   */
  public function unreadCount(User $user): int
  {
    return PortalNotification::query()
      ->where('user_id', $user->id)
      ->whereNull('read_at')
      ->count();
  }

  /**
   * Marque une notification comme lue.
   */
  public function markRead(User $user, PortalNotification $notification): void
  {
    if ($notification->user_id !== $user->id) {
      abort(403);
    }

    $notification->update(['read_at' => now()]);
  }

  /**
   * Marque toutes les notifications comme lues.
   */
  public function markAllRead(User $user): void
  {
    PortalNotification::query()
      ->where('user_id', $user->id)
      ->whereNull('read_at')
      ->update(['read_at' => now()]);
  }

  /**
   * Formate une notification pour le frontend.
   *
   * @return array<string, mixed>
   */
  public function payload(PortalNotification $notification): array
  {
    return [
      'id' => $notification->id,
      'type' => $notification->type->value,
      'title' => $notification->title,
      'body' => $notification->body,
      'action_url' => $notification->action_url,
      'action_label' => $notification->action_label,
      'read_at' => $notification->read_at?->format('d/m/Y H:i'),
      'is_read' => $notification->isRead(),
      'created_at' => $notification->created_at?->format('d/m/Y H:i'),
    ];
  }
}
