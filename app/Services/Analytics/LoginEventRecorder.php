<?php

namespace App\Services\Analytics;

use App\Models\LoginEvent;
use App\Models\User;
use App\Support\DeviceDetector;
use Illuminate\Http\Request;

/**
 * Enregistre une connexion réussie pour les statistiques appareils.
 */
class LoginEventRecorder
{
  /**
   * Persiste un événement de connexion.
   *
   * @param  User  $user  Utilisateur authentifié
   * @param  string  $guard  Garde d'authentification (member, admin…)
   * @param  Request|null  $request  Requête HTTP courante
   */
  public function record(User $user, string $guard, ?Request $request = null): LoginEvent
  {
    $request ??= request();
    $userAgent = $request?->userAgent();
    $device = DeviceDetector::parse($userAgent);

    return LoginEvent::query()->create([
      'user_id' => $user->id,
      'guard' => $guard,
      'device_type' => $device['device_type'],
      'browser' => $device['browser'],
      'platform' => $device['platform'],
      'ip_address' => $request?->ip(),
      'user_agent' => $userAgent ? mb_substr($userAgent, 0, 512) : null,
      'logged_in_at' => now(),
    ]);
  }
}
