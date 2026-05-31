<?php

namespace App\Services\Student;

use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Enrollment;
use App\Models\ProgramAccess;
use App\Models\User;
use App\Services\Ecap\EcapModuleCalendarAccessService;
use App\Services\Ecap\EcapPeriodAccessService;
use Illuminate\Support\Collection;

/**
 * Vérifie l'accès aux chapitres et gère la progression.
 */
class ChapterProgressService
{
  /**
   * @param  FormationJourneyService  $journeyService  Service de parcours
   * @param  ChapterGateService  $gateService  Prérequis tests / TP
   */
  public function __construct(
    private readonly FormationJourneyService $journeyService,
    private readonly ChapterGateService $gateService,
    private readonly EcapOnlineAccessService $ecapOnlineAccessService,
    private readonly EcapPeriodAccessService $ecapPeriodAccessService,
    private readonly EcapModuleCalendarAccessService $moduleCalendarAccessService,
  ) {}

  /**
   * Indique si un fidèle peut accéder à un chapitre.
   */
  public function canAccess(User $user, Chapter $chapter): bool
  {
    if (! $chapter->is_published) {
      return false;
    }

    $program = $chapter->course?->program;

    if (! $program) {
      return false;
    }

    $journey = $this->journeyService->forProgram($user, $program);
    $step = collect($journey['steps'])->firstWhere('id', $chapter->id);

    if ($program->slug === 'ecap' && ! $this->ecapOnlineAccessService->canUseOnlineProgression($user, $program)) {
      return $this->hasEcapReadAccess($user, $program);
    }

    if ($program->slug === 'ecap' && $chapter->course_module_id) {
      if ($this->moduleCalendarAccessService->isModuleClosed($user, (int) $chapter->course_module_id)) {
        return $this->moduleCalendarAccessService->allowsChapterReview($user, $chapter);
      }
    }

    if ($program->slug === 'ecap' && ! $this->ecapPeriodAccessService->canAccessChapter($user, $chapter)) {
      return false;
    }

    return $step !== null && $step['status'] !== 'locked';
  }

  /**
   * Vérifie que le fidèle a un accès ECAP visible (lecture seule présentiel).
   */
  private function hasEcapReadAccess(User $user, Program $program): bool
  {
    $access = ProgramAccess::query()
      ->where('user_id', $user->id)
      ->where('program_id', $program->id)
      ->first();

    if ($access === null) {
      return false;
    }

    return $access->is_open
      || $access->is_completed
      || $access->is_waived
      || $access->needs_admin_validation;
  }

  /**
   * Indique si le fidèle peut progresser (terminer étape, quiz, TP) sur ce chapitre.
   */
  public function canInteractOnline(User $user, Chapter $chapter): bool
  {
    if (! $this->canAccess($user, $chapter)) {
      return false;
    }

    $program = $chapter->course?->program;

    if ($program?->slug === 'ecap' && $chapter->course_module_id) {
      if ($this->moduleCalendarAccessService->isModuleClosed($user, (int) $chapter->course_module_id)) {
        return false;
      }
    }

    return $this->ecapOnlineAccessService->canUseOnlineProgression($user, $program);
  }

  /**
   * Marque un chapitre comme terminé pour le fidèle.
   *
   * @throws \RuntimeException Si les prérequis ne sont pas remplis
   */
  public function markCompleted(User $user, Chapter $chapter): void
  {
    if (! $this->canInteractOnline($user, $chapter)) {
      throw new \RuntimeException('La progression en ligne est désactivée pour votre mode ECAP présentiel.');
    }

    if ($this->gateService->isChapterCompleted($user, $chapter)) {
      return;
    }

    if (! $this->gateService->canCompleteChapter($user, $chapter)) {
      $reasons = $this->gateService->blockingReasons($user, $chapter);
      throw new \RuntimeException($reasons[0] ?? 'Prérequis non remplis pour cette étape.');
    }

    $enrollment = $this->resolveEnrollment($user, $chapter);

    if (! $enrollment) {
      return;
    }

    ChapterProgress::query()->updateOrCreate(
      [
        'enrollment_id' => $enrollment->id,
        'chapter_id' => $chapter->id,
      ],
      [
        'completed_at' => now(),
      ],
    );
  }

  /**
   * Démarre ou reprend la progression d'un chapitre.
   */
  public function startOrResume(User $user, Chapter $chapter): void
  {
    $enrollment = $this->resolveEnrollment($user, $chapter);

    if (! $enrollment) {
      return;
    }

    ChapterProgress::query()->firstOrCreate(
      [
        'enrollment_id' => $enrollment->id,
        'chapter_id' => $chapter->id,
      ],
      [
        'completed_at' => null,
      ],
    );
  }

  /**
   * Retourne le statut d'un chapitre pour le fidèle.
   */
  public function statusFor(User $user, Chapter $chapter): string
  {
    $program = $chapter->course?->program;

    if (! $program) {
      return 'locked';
    }

    $journey = $this->journeyService->forProgram($user, $program);
    $step = collect($journey['steps'])->firstWhere('id', $chapter->id);

    return $step['status'] ?? 'locked';
  }

  /**
   * Curriculum du cursus contenant le chapitre courant.
   *
   * @return array<int, array<string, mixed>>
   */
  public function curriculumFor(User $user, Chapter $chapter): array
  {
    $program = $chapter->course?->program;

    if (! $program) {
      return [];
    }

    $journey = $this->journeyService->forProgram($user, $program);

    return $journey['steps'];
  }

  /**
   * Chapitre suivant accessible après validation de l'étape courante.
   *
   * @return array{id: int, title: string}|null
   */
  public function nextChapterFor(User $user, Chapter $chapter): ?array
  {
    $steps = $this->curriculumFor($user, $chapter);

    foreach ($steps as $index => $step) {
      if ((int) $step['id'] !== (int) $chapter->id) {
        continue;
      }

      $next = $steps[$index + 1] ?? null;

      if ($next === null || ($next['status'] ?? 'locked') === 'locked') {
        return null;
      }

      return [
        'id' => (int) $next['id'],
        'title' => (string) $next['title'],
      ];
    }

    return null;
  }

  /**
   * Résout ou crée l'inscription du fidèle pour le programme du chapitre.
   */
  private function resolveEnrollment(User $user, Chapter $chapter): ?Enrollment
  {
    $program = $chapter->course?->program;

    if (! $program) {
      return null;
    }

    $course = $chapter->course;

    return Enrollment::query()->firstOrCreate(
      [
        'user_id' => $user->id,
        'program_id' => $program->id,
      ],
      [
        'course_id' => $course?->id,
        'status' => 'active',
        'enrolled_at' => now(),
      ],
    );
  }
}
