<?php

namespace App\Services\Ecap;

use App\Enums\AttemptStatus;
use App\Enums\EcapVacationRole;
use App\Models\AssessmentAttempt;
use App\Models\EcapStaffAssignment;
use App\Models\User;
use App\Services\Student\AssessmentAttemptGradingService;

/**
 * File d'attente des quiz à corriger pour les acteurs ECAP.
 */
class EcapQuizGradingService
{
  /**
   * @param  AssessmentAttemptGradingService  $gradingService  Service de correction
   */
  public function __construct(
    private readonly AssessmentAttemptGradingService $gradingService,
  ) {}

  /**
   * Tentatives en attente de correction visibles par l'acteur.
   *
   * @return array<int, array<string, mixed>>
   */
  public function pendingAttemptsForStaff(User $staff): array
  {
    return AssessmentAttempt::query()
      ->whereNotNull('submitted_at')
      ->with(['user', 'assessment.chapter', 'assessment.courseModule', 'gradingLockedBy'])
      ->latest('submitted_at')
      ->get()
      ->each(fn (AssessmentAttempt $attempt) => $this->gradingService->normalizePendingGradingStatus($attempt))
      ->filter(fn (AssessmentAttempt $attempt) => $this->gradingService->needsManualGrading($attempt))
      ->filter(fn (AssessmentAttempt $attempt) => $this->gradingService->canUserGrade($staff, $attempt))
      ->map(fn (AssessmentAttempt $attempt) => $this->listItemPayload($attempt, $staff))
      ->values()
      ->all();
  }

  /**
   * Libellé du périmètre de correction pour l'acteur (modules ou session).
   */
  public function graderScopeLabel(User $staff): string
  {
    $assignments = EcapStaffAssignment::query()
      ->where('user_id', $staff->id)
      ->where('is_active', true)
      ->whereIn('role', [
        EcapVacationRole::Teacher->value,
        EcapVacationRole::Supervisor->value,
        EcapVacationRole::Moderator->value,
      ])
      ->with('courseModule')
      ->get();

    if ($assignments->isEmpty()) {
      return 'Aucune affectation ECAP active.';
    }

    if ($assignments->contains(function ($assignment): bool {
      $role = $assignment->role instanceof EcapVacationRole
        ? $assignment->role
        : EcapVacationRole::from($assignment->role);

      return $role === EcapVacationRole::Moderator;
    })) {
      return 'Toute votre session ECAP (rôle modérateur).';
    }

    $moduleNames = $assignments
      ->pluck('courseModule.name')
      ->filter()
      ->unique()
      ->values();

    if ($assignments->contains(fn ($assignment) => $assignment->course_module_id === null)) {
      return 'Tous les modules de votre session ECAP.';
    }

    if ($moduleNames->isEmpty()) {
      return 'Modules ECAP qui vous sont affectés.';
    }

    return 'Modules : '.$moduleNames->join(', ').'.';
  }

  /**
   * Tentatives déjà corrigées (historique) visibles par l'acteur.
   *
   * @return array<int, array<string, mixed>>
   */
  public function gradedAttemptsForStaff(User $staff): array
  {
    return AssessmentAttempt::query()
      ->where('status', AttemptStatus::Graded->value)
      ->whereNotNull('submitted_at')
      ->with(['user', 'assessment.chapter', 'assessment.courseModule', 'gradedBy'])
      ->latest('updated_at')
      ->get()
      ->filter(fn (AssessmentAttempt $attempt) => $this->gradingService->hasWrittenAnswers($attempt))
      ->filter(fn (AssessmentAttempt $attempt) => $this->gradingService->canUserGrade($staff, $attempt))
      ->map(fn (AssessmentAttempt $attempt) => $this->historyItemPayload($attempt))
      ->values()
      ->all();
  }

  /**
   * Nombre de quiz en attente pour l'acteur.
   */
  public function pendingCountForStaff(User $staff): int
  {
    return count($this->pendingAttemptsForStaff($staff));
  }

  /**
   * Formate une ligne de liste.
   *
   * @return array<string, mixed>
   */
  private function listItemPayload(AssessmentAttempt $attempt, User $viewer): array
  {
    $lock = $this->gradingService->lockInfo($viewer, $attempt);

    return [
      'id' => $attempt->id,
      'student_name' => $attempt->user?->name,
      'assessment_title' => $attempt->assessment?->title,
      'chapter_title' => $attempt->assessment?->chapter?->title,
      'module_name' => $attempt->assessment?->courseModule?->name,
      'submitted_at' => $attempt->submitted_at?->format('d/m/Y H:i'),
      'status_label' => 'En attente',
      'lock' => $lock,
    ];
  }

  /**
   * Formate une ligne d'historique.
   *
   * @return array<string, mixed>
   */
  private function historyItemPayload(AssessmentAttempt $attempt): array
  {
    return [
      'id' => $attempt->id,
      'student_name' => $attempt->user?->name,
      'assessment_title' => $attempt->assessment?->title,
      'chapter_title' => $attempt->assessment?->chapter?->title,
      'module_name' => $attempt->assessment?->courseModule?->name,
      'submitted_at' => $attempt->submitted_at?->format('d/m/Y H:i'),
      'graded_at' => $attempt->updated_at?->format('d/m/Y H:i'),
      'graded_by_name' => $attempt->gradedBy?->name,
      'score' => $attempt->score !== null ? (float) $attempt->score : null,
      'passed' => (bool) $attempt->passed,
      'status_label' => 'Corrigé',
    ];
  }
}
