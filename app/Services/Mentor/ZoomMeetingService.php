<?php

namespace App\Services\Mentor;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Création de réunions Zoom via Server-to-Server OAuth.
 */
class ZoomMeetingService
{
  /**
   * Crée un lien Zoom de réunion pour un rendez-vous mentorat.
   */
  public function createMeetingLink(string $topic, Carbon $startsAt, int $durationMinutes = 60): ?string
  {
    if (! $this->isConfigured()) {
      return null;
    }

    $token = $this->fetchAccessToken();

    if (! $token) {
      return null;
    }

    $response = Http::withToken($token)
      ->post('https://api.zoom.us/v2/users/me/meetings', [
        'topic' => $topic,
        'type' => 2,
        'start_time' => $startsAt->copy()->utc()->toIso8601String(),
        'duration' => $durationMinutes,
        'timezone' => config('app.timezone', 'UTC'),
        'settings' => [
          'join_before_host' => false,
          'waiting_room' => true,
        ],
      ]);

    if (! $response->successful()) {
      return null;
    }

    return $response->json('join_url');
  }

  /**
   * Vérifie la configuration Zoom côté serveur.
   */
  public function isConfigured(): bool
  {
    return filled(config('zoom.account_id'))
      && filled(config('zoom.client_id'))
      && filled(config('zoom.client_secret'));
  }

  /**
   * Récupère un token OAuth Server-to-Server.
   */
  private function fetchAccessToken(): ?string
  {
    $response = Http::asForm()
      ->withBasicAuth((string) config('zoom.client_id'), (string) config('zoom.client_secret'))
      ->post('https://zoom.us/oauth/token', [
        'grant_type' => 'account_credentials',
        'account_id' => config('zoom.account_id'),
      ]);

    if (! $response->successful()) {
      return null;
    }

    return $response->json('access_token');
  }
}

