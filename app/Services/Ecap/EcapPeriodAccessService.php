<?php

namespace App\Services\Ecap;

use App\Enums\PeriodContentType;
use App\Models\AcademicSession;
use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\SessionPeriod;
use App\Models\SessionPeriodContent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Contrôle l'accès au contenu ECAP selon la période active de la génération.
 */
class EcapPeriodAccessService
{
  /**
   * Indique si un chapitre est accessible dans la période courante.
   */
  public function canAccessChapter(User $user, Chapter $chapter): bool
  {
    return $this->canAccessContent($user, PeriodContentType::Chapter, $chapter);
  }

  /**
   * Indique si un module est accessible dans la période courante.
   */
  public function canAccessModule(User $user, CourseModule $module): bool
  {
    return $this->canAccessContent($user, PeriodContentType::CourseModule, $module);
  }

  /**
   * Indique si une évaluation est accessible dans la période courante.
   */
  public function canAccessAssessment(User $user, Assessment $assessment): bool
  {
    return $this->canAccessContent($user, PeriodContentType::Assessment, $assessment);
  }

  /**
   * Message explicatif si l'accès est refusé pour cause de période.
   */
  public function denialMessageForUser(User $user): ?string
  {
    $session = $this->userEcapSession($user);

    if ($session === null || ! $this->hasPeriodSchedule($session)) {
      return null;
    }

    $current = $this->currentPeriod($session);

    if ($current !== null) {
      return null;
    }

    $next = $this->nextPeriod($session);

    if ($next !== null) {
      return 'La période « '.$next->display_label.' » débutera le '.$next->starts_on->format('d/m/Y').'.';
    }

    return 'Aucune période pédagogique n\'est active pour votre session ECAP.';
  }

  /**
   * Résumé de la période pour l'espace membre.
   *
   * @return array<string, mixed>|null
   */
  public function periodPayloadForUser(User $user): ?array
  {
    $session = $this->userEcapSession($user);

    if ($session === null || ! $this->hasPeriodSchedule($session)) {
      return null;
    }

    $current = $this->currentPeriod($session);

    return [
      'has_schedule' => true,
      'current' => $current ? [
        'name' => $current->display_label,
        'type' => $current->type?->value,
        'starts_on' => $current->starts_on?->format('d/m/Y'),
        'ends_on' => $current->ends_on?->format('d/m/Y'),
      ] : null,
      'message' => $this->denialMessageForUser($user),
    ];
  }

  /**
   * Session ECAP du fidèle (inscription active).
   */
  public function userEcapSession(User $user): ?AcademicSession
  {
    $enrollment = Enrollment::query()
      ->where('user_id', $user->id)
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->whereNotNull('academic_session_id')
      ->with('academicSession.sessionPeriods')
      ->latest('id')
      ->first();

    return $enrollment?->academicSession;
  }

  /**
   * Indique si des périodes sont configurées pour la session.
   */
  public function hasPeriodSchedule(AcademicSession $session): bool
  {
    if ($session->relationLoaded('sessionPeriods')) {
      return $session->sessionPeriods->where('is_active', true)->isNotEmpty();
    }

    return $session->sessionPeriods()
      ->where('is_active', true)
      ->exists();
  }

  /**
   * Période active à la date du jour.
   */
  public function currentPeriod(AcademicSession $session): ?SessionPeriod
  {
    $periods = $session->relationLoaded('sessionPeriods')
      ? $session->sessionPeriods
      : $session->sessionPeriods()->get();

    return $periods
      ->where('is_active', true)
      ->sortBy('sort_order')
      ->first(fn (SessionPeriod $period) => $period->isActiveNow());
  }

  /**
   * Prochaine période à venir.
   */
  public function nextPeriod(AcademicSession $session): ?SessionPeriod
  {
    $today = now()->startOfDay();

    return $session->sessionPeriods()
      ->where('is_active', true)
      ->whereDate('starts_on', '>', $today)
      ->orderBy('starts_on')
      ->first();
  }

  /**
   * Vérifie l'accès à un contenu selon la période active.
   */
  private function canAccessContent(User $user, PeriodContentType $type, Model $model): bool
  {
    $session = $this->userEcapSession($user);

    if ($session === null || ! $this->hasPeriodSchedule($session)) {
      return true;
    }

    $current = $this->currentPeriod($session);

    if ($current === null) {
      return false;
    }

    return SessionPeriodContent::query()
      ->where('session_period_id', $current->id)
      ->where('content_type', $type->value)
      ->where('content_id', $model->getKey())
      ->exists();
  }
}
