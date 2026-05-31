<?php

namespace App\Services\Public;

use App\Models\AcademicSession;

/**
 * Détermine la disponibilité des inscriptions publiques (fenêtre ECAP).
 */
class RegistrationAvailabilityService
{
  public const STATUS_DISABLED = 'disabled';

  public const STATUS_UPCOMING = 'upcoming';

  public const STATUS_OPEN = 'open';

  public const STATUS_CLOSED = 'closed';

  /**
   * Session ECAP de référence pour l'affichage public (inscriptions, messages).
   */
  public function currentEcapSession(): ?AcademicSession
  {
    $sessions = AcademicSession::query()
      ->where('is_active', true)
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->with('program')
      ->withCount('moduleSchedules')
      ->get();

    if ($sessions->isEmpty()) {
      return null;
    }

    $open = $sessions->first(fn (AcademicSession $session) => $session->isRegistrationOpen());

    if ($open !== null) {
      return $open;
    }

    $upcoming = $sessions
      ->filter(fn (AcademicSession $session) => $session->registration_opens_at?->isFuture())
      ->sortBy('registration_opens_at')
      ->first();

    if ($upcoming !== null) {
      return $upcoming;
    }

    return $sessions->sortByDesc('registration_closes_at')->first();
  }

  /**
   * Indique si le formulaire d'inscription public est accessible.
   */
  public function isRegistrationFormOpen(): bool
  {
    return $this->registrationStatus() === self::STATUS_OPEN;
  }

  /**
   * Indique si le countdown doit s'afficher sur la page d'accueil.
   */
  public function isCountdownVisible(): bool
  {
    return $this->isRegistrationFormOpen();
  }

  /**
   * Statut courant des inscriptions.
   */
  public function registrationStatus(?AcademicSession $session = null): string
  {
    $session ??= $this->currentEcapSession();

    if ($session === null || ! $session->is_active) {
      return self::STATUS_DISABLED;
    }

    if ($session->isRegistrationOpen()) {
      return self::STATUS_OPEN;
    }

    $opensAt = $session->registration_opens_at;

    if ($opensAt !== null && $opensAt->isFuture()) {
      return self::STATUS_UPCOMING;
    }

    return self::STATUS_CLOSED;
  }

  /**
   * Données partagées avec le front public (landing, header, inscription).
   *
   * @return array<string, mixed>
   */
  public function publicPayload(): array
  {
    $session = $this->currentEcapSession();
    $status = $this->registrationStatus($session);
    $secondsUntilOpen = null;

    if ($status === self::STATUS_UPCOMING && $session?->registration_opens_at?->isFuture()) {
      $secondsUntilOpen = max(0, (int) now()->diffInSeconds($session->registration_opens_at, false));
    }

    return [
      'is_open' => $status === self::STATUS_OPEN,
      'status' => $status,
      'message' => $this->statusMessage($status, $session),
      'registration_opens_at' => $session?->registration_opens_at?->format('d/m/Y H:i'),
      'registration_closes_at' => $session?->registration_closes_at?->format('d/m/Y H:i'),
      'seconds_until_open' => $secondsUntilOpen,
      'session_name' => $session?->name,
    ];
  }

  /**
   * Bandeau ECAP avec countdown (uniquement si inscriptions ouvertes).
   *
   * @return array<string, mixed>|null
   */
  public function ecapCountdownPayload(): ?array
  {
    if (! $this->isCountdownVisible()) {
      return null;
    }

    $session = $this->currentEcapSession();

    if ($session === null) {
      return null;
    }

    $closesAt = $session->registration_closes_at;
    $secondsRemaining = null;

    if ($closesAt !== null && $closesAt->isFuture()) {
      $secondsRemaining = max(0, (int) now()->diffInSeconds($closesAt, false));
    }

    return [
      'id' => $session->id,
      'name' => $session->name,
      'code' => $session->code,
      'generation_number' => $session->generation_number,
      'starts_on' => $session->starts_on?->format('d/m/Y'),
      'ends_on' => $session->ends_on?->format('d/m/Y'),
      'registration_opens_at' => $session->registration_opens_at?->toIso8601String(),
      'registration_closes_at' => $closesAt?->toIso8601String(),
      'is_registration_open' => true,
      'registration_status' => self::STATUS_OPEN,
      'seconds_remaining' => $secondsRemaining,
      'modules_scheduled' => $session->module_schedules_count ?? 0,
    ];
  }

  /**
   * Résumé du statut inscriptions pour l'administration Filament.
   */
  public function adminStatusForSession(AcademicSession $session): string
  {
    if (! $session->is_active) {
      return 'Session inactive : aucune inscription publique ni countdown sur le portail.';
    }

    if (! $session->isEcap()) {
      return 'Cette session n\'est pas rattachée au cursus ECAP.';
    }

    $status = $this->registrationStatus($session);
    $hint = match ($status) {
      self::STATUS_OPEN => 'Le formulaire /inscription est ouvert et le bouton « S\'inscrire » y mène directement.',
      self::STATUS_UPCOMING => 'Le portail affiche un message d\'attente et une modale avec la date d\'ouverture.',
      self::STATUS_CLOSED => 'Le portail indique que la période d\'inscription est terminée.',
      default => 'Aucune fenêtre d\'inscription configurée pour le portail.',
    };

    return $this->statusMessage($status, $session).' '.$hint;
  }

  /**
   * Message utilisateur selon le statut.
   */
  private function statusMessage(string $status, ?AcademicSession $session): string
  {
    return match ($status) {
      self::STATUS_OPEN => 'Les inscriptions sont ouvertes.',
      self::STATUS_UPCOMING => $session?->registration_opens_at
        ? 'Les inscriptions n\'ont pas encore débuté. Ouverture prévue le '.$session->registration_opens_at->format('d/m/Y à H:i').'.'
        : 'Les inscriptions n\'ont pas encore débuté. Revenez prochainement.',
      self::STATUS_CLOSED => $session?->registration_closes_at
        ? 'Les inscriptions sont closes depuis le '.$session->registration_closes_at->format('d/m/Y à H:i').'.'
        : 'Les inscriptions sont actuellement closes.',
      default => 'Les inscriptions en ligne ne sont pas disponibles pour le moment.',
    };
  }
}
