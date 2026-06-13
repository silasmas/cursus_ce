<?php

namespace App\Listeners;

use App\Services\Analytics\LoginEventRecorder;
use Illuminate\Auth\Events\Login;

/**
 * Enregistre chaque connexion réussie (portail fidèle ou admin).
 */
class RecordUserLogin
{
  /**
   * @param  LoginEventRecorder  $recorder  Service d'enregistrement
   */
  public function __construct(
    private readonly LoginEventRecorder $recorder,
  ) {}

  /**
   * Gère l'événement Laravel Login.
   *
   * @param  Login  $event  Connexion réussie
   */
  public function handle(Login $event): void
  {
    if (! $event->user instanceof \App\Models\User) {
      return;
    }

    $this->recorder->record($event->user, $event->guard);
  }
}
