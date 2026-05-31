<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

/**
 * Retours visuels Filament : toast, cloche admin et rafraîchissement Livewire.
 */
trait SendsFilamentOperationFeedback
{
  /**
   * Affiche un toast, enregistre dans la cloche admin et notifie le composant Livewire.
   *
   * @param  Notification  $notification  Notification Filament configurée
   */
  protected function sendFilamentFeedback(Notification $notification): void
  {
    $notification->send();

    $user = Filament::auth()->user();

    if ($user instanceof Authenticatable) {
      try {
        // notifyNow : pas de file d'attente (sendToDatabase() exige un worker / connexion valide).
        $user->notifyNow($notification->toDatabase());
      } catch (Throwable $exception) {
        report($exception);
      }
    }

    $this->dispatch('notificationSent', notification: $notification->toArray());
  }
}
