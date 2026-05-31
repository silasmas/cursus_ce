<?php

namespace App\Services\Portal;

use App\Mail\PortalNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi des e-mails de notification portail.
 */
class PortalMailService
{
  /**
   * Envoie un e-mail de notification à un utilisateur si l'adresse est valide.
   */
  public function sendToUser(
    User $user,
    string $subject,
    string $title,
    string $body,
    ?string $actionUrl = null,
    ?string $actionLabel = null,
  ): void {
    if (! filled($user->email)) {
      return;
    }

    try {
      Mail::to($user->email)->send(new PortalNotificationMail(
        $subject,
        $title,
        $body,
        $this->absoluteUrl($actionUrl),
        $actionLabel,
      ));
    } catch (\Throwable $exception) {
      Log::warning('Échec envoi e-mail portail', [
        'user_id' => $user->id,
        'email' => $user->email,
        'subject' => $subject,
        'error' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * Convertit un chemin relatif en URL absolue.
   */
  private function absoluteUrl(?string $path): ?string
  {
    if (! filled($path)) {
      return null;
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
      return $path;
    }

    return url($path);
  }
}
