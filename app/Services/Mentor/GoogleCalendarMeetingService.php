<?php

namespace App\Services\Mentor;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Crée des événements Google Calendar avec lien Google Meet.
 */
class GoogleCalendarMeetingService
{
  /**
   * Crée un événement calendrier et retourne le lien Meet.
   */
  public function createMeetLink(string $topic, Carbon $startsAt, int $durationMinutes = 60): ?string
  {
    if (! $this->isConfigured()) {
      return null;
    }

    $token = $this->fetchAccessToken();

    if (! $token) {
      return null;
    }

    $timezone = (string) config('google_calendar.timezone', config('app.timezone', 'UTC'));
    $endsAt = $startsAt->copy()->addMinutes($durationMinutes);
    $calendarId = rawurlencode((string) config('google_calendar.calendar_id', 'primary'));
    $requestId = 'phila-'.Str::uuid()->toString();

    $response = Http::withToken($token)
      ->post(
        "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events?conferenceDataVersion=1",
        [
          'summary' => $topic,
          'start' => [
            'dateTime' => $startsAt->copy()->timezone($timezone)->format('Y-m-d\TH:i:s'),
            'timeZone' => $timezone,
          ],
          'end' => [
            'dateTime' => $endsAt->copy()->timezone($timezone)->format('Y-m-d\TH:i:s'),
            'timeZone' => $timezone,
          ],
          'conferenceData' => [
            'createRequest' => [
              'requestId' => $requestId,
              'conferenceSolutionKey' => [
                'type' => 'hangoutsMeet',
              ],
            ],
          ],
        ],
      );

    if (! $response->successful()) {
      return null;
    }

    $hangoutLink = $response->json('hangoutLink');

    if (filled($hangoutLink)) {
      return $hangoutLink;
    }

    $entryPoints = $response->json('conferenceData.entryPoints', []);

    foreach ($entryPoints as $entryPoint) {
      if (($entryPoint['entryPointType'] ?? null) === 'video' && filled($entryPoint['uri'] ?? null)) {
        return $entryPoint['uri'];
      }
    }

    return null;
  }

  /**
   * Vérifie la présence des identifiants OAuth Google Calendar.
   */
  public function isConfigured(): bool
  {
    return filled(config('google_calendar.client_id'))
      && filled(config('google_calendar.client_secret'))
      && filled(config('google_calendar.refresh_token'));
  }

  /**
   * Obtient un access token via refresh token OAuth2.
   */
  private function fetchAccessToken(): ?string
  {
    $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
      'client_id' => config('google_calendar.client_id'),
      'client_secret' => config('google_calendar.client_secret'),
      'refresh_token' => config('google_calendar.refresh_token'),
      'grant_type' => 'refresh_token',
    ]);

    if (! $response->successful()) {
      return null;
    }

    return $response->json('access_token');
  }
}
