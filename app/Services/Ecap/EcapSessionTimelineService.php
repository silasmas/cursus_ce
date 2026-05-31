<?php

namespace App\Services\Ecap;

use App\Enums\CalendarItemType;
use App\Enums\EcapVacationRole;
use App\Models\AcademicSession;
use App\Models\EcapStaffAssignment;
use App\Models\SessionModuleSchedule;
use App\Models\SessionPeriod;
use App\Models\User;
use Carbon\Carbon;

/**
 * Construit la timeline du calendrier ECAP pour le portail fidèle.
 */
class EcapSessionTimelineService
{
  /**
   * @param  EcapPeriodAccessService  $periodAccessService  Session via inscription
   * @param  VacationQuestionService  $vacationQuestionService  Session via profil
   * @param  EcapModuleCountdownService  $countdownService  Décomptes modules
   */
  public function __construct(
    private readonly EcapPeriodAccessService $periodAccessService,
    private readonly VacationQuestionService $vacationQuestionService,
    private readonly EcapModuleCountdownService $countdownService,
  ) {}

  /**
   * Entrées timeline ordonnées pour la session du fidèle.
   *
   * @return array{session_name: string|null, session_id: int|null, has_session: bool, items: array<int, array<string, mixed>>}
   */
  public function forUser(User $user): array
  {
    $session = $this->resolveSession($user);

    if ($session === null) {
      return [
        'session_name' => null,
        'session_id' => null,
        'has_session' => false,
        'items' => [],
      ];
    }

    $today = now()->startOfDay();
    $items = [];

    $periods = $session->sessionPeriods()
      ->where('is_active', true)
      ->orderBy('starts_on')
      ->get();

    foreach ($periods as $period) {
      $items[] = $this->mapPeriodItem($period, $today);
    }

    $schedules = SessionModuleSchedule::query()
      ->where('academic_session_id', $session->id)
      ->with(['courseModule', 'sessionPeriod'])
      ->orderBy('starts_on')
      ->orderBy('sort_order')
      ->get();

    foreach ($schedules as $schedule) {
      $items[] = $this->mapScheduleItem($schedule, $today);
    }

    usort($items, function (array $a, array $b) {
      return strcmp($a['sort_key'], $b['sort_key']);
    });

    return [
      'session_name' => $session->name,
      'session_id' => $session->id,
      'has_session' => true,
      'items' => array_values($items),
    ];
  }

  /**
   * Résout la session ECAP du fidèle (inscription ou profil).
   */
  private function resolveSession(User $user): ?AcademicSession
  {
    $session = $this->periodAccessService->userEcapSession($user);

    if ($session !== null) {
      return $session;
    }

    $session = $this->vacationQuestionService->studentSession($user);

    if ($session !== null) {
      return $session;
    }

    $sessionId = $user->profile?->academic_session_id;

    if ($sessionId !== null) {
      $fromProfile = AcademicSession::query()
        ->whereKey($sessionId)
        ->where('is_active', true)
        ->first();

      if ($fromProfile !== null) {
        return $fromProfile;
      }
    }

    $staffSessionId = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->whereIn('role', [
        EcapVacationRole::Supervisor->value,
        EcapVacationRole::Moderator->value,
        EcapVacationRole::Teacher->value,
      ])
      ->orderByDesc('academic_session_id')
      ->value('academic_session_id');

    if ($staffSessionId === null) {
      return null;
    }

    return AcademicSession::query()->find($staffSessionId);
  }

  /**
   * @return array<string, mixed>
   */
  private function mapPeriodItem(SessionPeriod $period, Carbon $today): array
  {
    $starts = $period->starts_on?->copy()->startOfDay();
    $ends = $period->ends_on?->copy()->endOfDay();

    return [
      'id' => 'period-'.$period->id,
      'type' => 'period',
      'title' => $period->display_label,
      'subtitle' => 'Période session',
      'description' => null,
      'starts_on' => $starts?->format('d/m/Y'),
      'ends_on' => $ends?->format('d/m/Y'),
      'status' => $this->resolveStatus($starts, $ends, $today),
      'sort_key' => $starts?->format('Y-m-d') ?? '9999-12-31',
      'countdown' => null,
    ];
  }

  /**
   * @return array<string, mixed>
   */
  private function mapScheduleItem(SessionModuleSchedule $schedule, Carbon $today): array
  {
    $starts = $schedule->starts_on?->copy()->startOfDay();
    $ends = $schedule->ends_on?->copy()->endOfDay();
    $isActivity = $schedule->item_type === CalendarItemType::Activity;
    $isModule = ! $isActivity;

    return [
      'id' => 'schedule-'.$schedule->id,
      'type' => $isActivity ? 'activity' : 'module',
      'title' => $schedule->displayLabel(),
      'subtitle' => $isActivity ? 'Activité' : 'Module de cours',
      'description' => $schedule->description,
      'starts_on' => $starts?->format('d/m/Y'),
      'ends_on' => $ends?->format('d/m/Y'),
      'status' => $this->resolveStatus($starts, $ends, $today),
      'sort_key' => $starts?->format('Y-m-d') ?? '9999-12-31',
      'countdown' => $isModule ? $this->countdownService->buildFromEndDate($ends?->copy()->startOfDay(), $today) : null,
    ];
  }

  /**
   * Statut visuel : past | current | upcoming.
   */
  private function resolveStatus(?Carbon $starts, ?Carbon $ends, Carbon $today): string
  {
    if ($starts === null && $ends === null) {
      return 'upcoming';
    }

    if ($ends !== null && $today->gt($ends)) {
      return 'past';
    }

    if ($starts !== null && $today->lt($starts)) {
      return 'upcoming';
    }

    return 'current';
  }
}
