<?php

namespace App\Services\Ecap;

use App\Models\SessionModuleSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcule le décompte d'accès aux modules ECAP (calendrier session).
 */
class EcapModuleCountdownService
{
  /**
   * @param  EcapPeriodAccessService  $periodAccessService  Session du fidèle
   */
  public function __construct(
    private readonly EcapPeriodAccessService $periodAccessService,
  ) {}

  /**
   * Décomptes indexés par identifiant de module de cours.
   *
   * @return array<int, array<string, mixed>>
   */
  public function countdownsByModuleForUser(User $user): array
  {
    $session = $this->periodAccessService->userEcapSession($user);

    if ($session === null) {
      $sessionId = $user->profile?->academic_session_id;
      if ($sessionId === null) {
        return [];
      }
    } else {
      $sessionId = $session->id;
    }

    $today = now()->startOfDay();
    $map = [];

    $schedules = SessionModuleSchedule::query()
      ->where('academic_session_id', $sessionId)
      ->whereNotNull('course_module_id')
      ->get();

    foreach ($schedules as $schedule) {
      $moduleId = (int) $schedule->course_module_id;
      $countdown = $this->buildFromEndDate($schedule->ends_on?->copy()->startOfDay(), $today);

      if ($countdown !== null) {
        $map[$moduleId] = $countdown;
      }
    }

    return $map;
  }

  /**
   * Construit le payload de décompte à partir de la date de fin d'accès.
   *
   * @return array<string, mixed>|null
   */
  public function buildFromEndDate(?Carbon $endsOn, ?Carbon $today = null): ?array
  {
    if ($endsOn === null) {
      return null;
    }

    $today = ($today ?? now())->copy()->startOfDay();
    $endDay = $endsOn->copy()->startOfDay();

    if ($today->gt($endDay)) {
      return [
        'label' => 'Accès au module fermé',
        'days_remaining' => 0,
        'urgency' => 'closed',
        'access_open' => false,
      ];
    }

    $daysRemaining = (int) $today->diffInDays($endDay, false);

    if ($daysRemaining <= 1) {
      $label = $daysRemaining === 0
        ? 'Dernier jour d\'accès — se termine aujourd\'hui'
        : 'Plus qu\'1 jour pour terminer ce module';

      return [
        'label' => $label,
        'days_remaining' => $daysRemaining,
        'urgency' => 'critical',
        'access_open' => true,
      ];
    }

    if ($daysRemaining < 3) {
      return [
        'label' => "Plus que {$daysRemaining} jours pour accéder à ce module",
        'days_remaining' => $daysRemaining,
        'urgency' => 'warning',
        'access_open' => true,
      ];
    }

    return [
      'label' => "Encore {$daysRemaining} jours d'accès à ce module",
      'days_remaining' => $daysRemaining,
      'urgency' => 'ok',
      'access_open' => true,
    ];
  }

  /**
   * Décompte pour un module précis.
   *
   * @return array<string, mixed>|null
   */
  public function forModule(User $user, int $courseModuleId): ?array
  {
    return $this->countdownsByModuleForUser($user)[$courseModuleId] ?? null;
  }
}
